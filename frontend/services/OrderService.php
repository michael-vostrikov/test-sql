<?php

declare(strict_types=1);

namespace frontend\services;

use common\models\Customer;
use common\models\OrderDetail;
use common\models\OrderHeader;
use common\repositories\CustomerRepository;
use common\repositories\LockService;
use common\repositories\OrderRepository;
use frontend\forms\CreateOrderDto;
use yii\db\Connection;

class OrderService
{
    public function __construct(
        private readonly Connection $dbConnection,
        private readonly CustomerRepository $customerRepository,
        private readonly OrderRepository $orderRepository,
        private readonly LockService $lockService,
    ) {
    }

    public function create(CreateOrderDto $dto): string
    {
        $transaction = $this->dbConnection->beginTransaction();

        $customer = $this->customerRepository->findByExternalId($dto->external_id, needLock: true)
            ?? new Customer(['external_id' => $dto->external_id]);

        if ($dto->order_num === null) {
            $currentYear = date('Y');
            $parts = $customer->last_order !== null ? explode('/', $customer->last_order) : [$currentYear, '000000'];
            $number = (int) $parts[count($parts) - 1];
            $year = substr($parts[count($parts) - 2], -4);
            $nextNumber = $year === $currentYear ? $number + 1 : 1;

            $customer->last_order = $dto->external_id . $currentYear . '/' . sprintf('%06d', $nextNumber);
            $dto->order_num = $customer->last_order;
        }
        $customer->save();

        $transaction->commit();
        $this->lockService->release(Customer::class, $dto->external_id);

        $transaction = $this->dbConnection->beginTransaction();

        $order = $this->orderRepository->findByOrderNum($dto->order_num, needLock: true)
            ?? new OrderHeader(['order_num' => $dto->order_num]);

        $order->customer_id = $customer->id;
        $order->order_date = $dto->order_date;
        $order->save();

        OrderDetail::deleteAll(['order_id' => $order->id]);
        foreach ($dto->order_details as $i => $detailDto) {
            $detail = new OrderDetail();
            $detail->order_id = $order->id;
            $detail->line_num = $i + 1;
            $detail->sku = $detailDto->sku;
            $detail->price = $detailDto->price;
            $detail->qty = $detailDto->qty;
            $detail->save();
        }

        $transaction->commit();

        return $order->order_num;
    }

    public function createWithProcedure(CreateOrderDto $dto): ?string
    {
        $params = [
            null,
            $dto->external_id,
            $dto->order_num,
            $dto->order_date,
        ];
        unset($params[0]);
        $sql = implode(',', array_fill(0, count($dto->order_details), '(?,?,?)'));

        foreach ($dto->order_details as $detail) {
            $params[] = $detail->sku;
            $params[] = $detail->price;
            $params[] = $detail->qty;
        }

        $stmt = $this->dbConnection->createCommand("SELECT save_order((
            ?,
            ?,
            ?,
            ARRAY[$sql]::details_compose_tp[]
        ))", $params);
        $data = $stmt->queryColumn();

        return $data[0] ?? null;
    }
}
