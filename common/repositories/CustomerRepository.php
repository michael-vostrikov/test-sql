<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\Customer;

class CustomerRepository
{
    public function __construct(private readonly LockService $lockService)
    {
    }

    public function findByExternalId(?string $externalId, bool $needLock): ?Customer
    {
        if ($externalId == null) {
            return null;
        }

        if ($needLock) {
            $this->lockService->lock(Customer::class, $externalId);
        }

        return Customer::findOne(['external_id' => $externalId]);
    }
}
