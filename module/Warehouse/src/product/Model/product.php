<?php

namespace Warehouse\Product\Model;

class Product
{
    public $ProductID;
    public $ProductCode;
    public $ProductName;
    public $CategoryID;
    public $UnitPrice;
    public $ReorderLevel;
    public $IsActive;
    public $CreatedDate;

    public function exchangeArray(array $data)
    {
        $this->ProductID     = $data['ProductID'] ?? null;
        $this->ProductCode   = $data['ProductCode'] ?? null;
        $this->ProductName   = $data['ProductName'] ?? null;
        $this->CategoryID    = $data['CategoryID'] ?? null;
        $this->UnitPrice     = $data['UnitPrice'] ?? null;
        $this->ReorderLevel  = $data['ReorderLevel'] ?? 10;
        $this->IsActive      = $data['IsActive'] ?? 1;
        $this->CreatedDate   = $data['CreatedDate'] ?? null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}