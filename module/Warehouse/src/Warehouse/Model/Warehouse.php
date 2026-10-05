<?php

namespace Warehouse\Warehouse\Model;

class Warehouse
{
    public $WarehouseID;
    public $WarehouseCode;
    public $WarehouseName;
    public $Location;
    public $IsActive;
    public $CreatedDate;

    public function exchangeArray(array $data)
    {
        $this->WarehouseID   = $data['WarehouseID'] ?? null;
        $this->WarehouseCode = $data['WarehouseCode'] ?? null;
        $this->WarehouseName = $data['WarehouseName'] ?? null;
        $this->Location      = $data['Location'] ?? null;
        $this->IsActive      = $data['IsActive'] ?? 1;
        $this->CreatedDate   = $data['CreatedDate'] ?? null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}