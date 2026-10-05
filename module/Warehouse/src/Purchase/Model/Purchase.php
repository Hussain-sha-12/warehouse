<?php

namespace Warehouse\Purchase\Model;

class Purchase
{
    public $PurchaseOrderID;
    public $PurchaseOrderNumber;
    public $SupplierID;
    public $WarehouseID;
    public $OrderDate;
    public $Status;
    public $TotalAmount;
    public $CreatedBy;

    public function exchangeArray(array $data)
    {
        $this->PurchaseOrderID     = $data['PurchaseOrderID'] ?? null;
        $this->PurchaseOrderNumber = $data['PurchaseOrderNumber'] ?? null;
        $this->SupplierID          = $data['SupplierID'] ?? null;
        $this->WarehouseID         = $data['WarehouseID'] ?? null;
        $this->OrderDate           = $data['OrderDate'] ?? null;
        $this->Status              = $data['Status'] ?? 'Pending';
        $this->TotalAmount         = $data['TotalAmount'] ?? 0;
        $this->CreatedBy           = $data['CreatedBy'] ?? null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}