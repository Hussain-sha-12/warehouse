<?php

namespace Warehouse\Inventory\Model;

class Inventory
{
    public $InventoryID;
    public $ProductID;
    public $Quantity;
    public $ReorderLevel;
    public $LastUpdated;

    public function exchangeArray(array $data)
    {
        $this->InventoryID    = $data['InventoryID'] ?? null;
        $this->ProductID     = $data['ProductID'] ?? null;
        $this->Quantity      = $data['Quantity'] ?? 0;
        $this->ReorderLevel  = $data['ReorderLevel'] ?? 0;
        $this->LastUpdated   = $data['LastUpdated'] ?? null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}