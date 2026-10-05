<?php

namespace Warehouse\StockIn\Model;

class StockIn
{
    public $PurchaseOrderID;
    public $PurchaseOrderItemID;
    public $ProductID;
    public $Quantity;
    public $UnitCost;

    public function exchangeArray(array $data)
    {
        $this->PurchaseOrderID     = $data['PurchaseOrderID'] ?? null;
        $this->PurchaseOrderItemID = $data['PurchaseOrderItemID'] ?? null;
        $this->ProductID           = $data['ProductID'] ?? null;
        $this->Quantity             = $data['Quantity'] ?? 0;
        $this->UnitCost             = $data['UnitCost'] ?? 0;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}