<?php

namespace frontend\controllers;

use common\controllers\BaseApiController;
use frontend\forms\CreateOrderDto;
use frontend\forms\OrderDetailDto;
use frontend\services\OrderService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class OrderController extends BaseApiController
{
    public function __construct(
        $id,
        $module,
        $config,
        private readonly OrderService $orderService,
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'create-with-procedure'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                ],
            ],
        ]);
    }

    private function createDtoByRequest(): CreateOrderDto
    {
        $inputData = $this->request->post();

        return new CreateOrderDto(
            $inputData['external_id'],
            $inputData['order_num'],
            $inputData['order_date'],
            array_map(static fn($el) => new OrderDetailDto(
                $el['sku'],
                $el['price'],
                $el['qty'],
            ),  $inputData['order_details']),
        );
    }

    public function actionCreate(): Response
    {
        $dto = $this->createDtoByRequest();

        $t1 = microtime(true);
        $orderNum = $this->orderService->create($dto);
        $t2 = microtime(true);

        return $this->successResponse(['orderNum' => $orderNum, 'serverTime' => (int) (($t2 - $t1) * 1000)]);
    }

    public function actionCreateWithProcedure(): Response
    {
        $dto = $this->createDtoByRequest();

        $t1 = microtime(true);
        $orderNum = $this->orderService->createWithProcedure($dto);
        $t2 = microtime(true);

        return $this->successResponse(['orderNum' => $orderNum, 'serverTime' => (int) (($t2 - $t1) * 1000)]);
    }
}
