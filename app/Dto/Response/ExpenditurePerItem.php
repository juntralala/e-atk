<?php

namespace App\Dto\Response;

class ExpenditurePerItem {
    public function __construct(
        public string $itemName,
        public int $count,
        public string $measurementUnit,
        public float $pricePerUnit,
        public float $expenditure
    ){}
}