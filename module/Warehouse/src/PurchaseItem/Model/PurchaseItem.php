<?php

namespace Warehouse\PurchaseItem\Model;

class PurchaseItem
{
    public $PurchaseOrderItemID;
    public $PurchaseOrderID;
    public $ProductID;
    public $Quantity;
    public $UnitCost;
    public $TotalAmount;

    public function exchangeArray(array $data)
    {
        $this->PurchaseOrderItemID = $data['PurchaseOrderItemID'] ?? null;
        $this->PurchaseOrderID     = $data['PurchaseOrderID'] ?? null;
        $this->ProductID           = $data['ProductID'] ?? null;
        $this->Quantity            = $data['Quantity'] ?? 0;
        $this->UnitCost            = $data['UnitCost'] ?? 0;
        $this->TotalAmount         = $data['TotalAmount'] ?? 0;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}