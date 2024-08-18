<?php

namespace frontend\forms;

class CreateOrderDto
{
    public function __construct(
        public string $external_id,
        public ?string $order_num,
        public string $order_date,
        /** @var OrderDetailDto[] */
        public array $order_details,
    ) {
    }
}
