<?php

namespace KayiwaRahim\Relworx;

use KayiwaRahim\Relworx\Services\BalanceService;
use KayiwaRahim\Relworx\Services\CollectionService;
use KayiwaRahim\Relworx\Services\DisbursementService;
use KayiwaRahim\Relworx\Services\TransactionService;

class RelworxManager
{
    protected RelworxClient $client;

    public function __construct(array $config)
    {
        $this->client = new RelworxClient($config);
    }

    public function collections(): CollectionService { return new CollectionService($this->client); }
    public function disbursements(): DisbursementService { return new DisbursementService($this->client); }
    public function balance(): BalanceService { return new BalanceService($this->client); }
    public function transactions(): TransactionService { return new TransactionService($this->client); }
}
