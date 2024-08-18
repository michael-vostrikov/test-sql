<?php

namespace frontend\forms;

class OrderDetailDto
{
    public function __construct(
        public int $sku,
        public string $price,
        public int $qty,
    ) {
    }
}
