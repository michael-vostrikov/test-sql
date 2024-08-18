<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\OrderHeader;

class OrderRepository
{
    public function __construct(private readonly LockService $lockService)
    {
    }

    public function findByOrderNum(?string $orderNum, bool $needLock): ?OrderHeader
    {
        if ($orderNum == null) {
            return null;
        }

        if ($needLock) {
            $this->lockService->lock(OrderHeader::class, $orderNum);
        }

        return OrderHeader::findOne(['order_num' => $orderNum]);
    }
}
