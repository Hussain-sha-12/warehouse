<?php

namespace Warehouse\InventoryReport\Model;

class InventoryReport
{
    public $ProductID;
    public $ProductCode;
    public $ProductName;
    public $Quantity;
    public $ReorderLevel;
    public $LastUpdated;

    public function exchangeArray(array $data)
    {
        $this->ProductID     = $data['ProductID'] ?? null;
        $this->ProductCode   = $data['ProductCode'] ?? null;
        $this->ProductName   = $data['ProductName'] ?? null;
        $this->Quantity      = $data['Quantity'] ?? 0;
        $this->ReorderLevel  = $data['ReorderLevel'] ?? 0;
        $this->LastUpdated   = $data['LastUpdated'] ?? null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}