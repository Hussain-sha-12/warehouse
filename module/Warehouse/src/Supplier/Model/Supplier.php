<?php

namespace Warehouse\Supplier\Model;

class Supplier
{
    public $SupplierID;
    public $SupplierCode;
    public $SupplierName;
    public $Phone;
    public $Email;
    public $Address;
    public $IsActive;
    public $CreatedDate;

    public function exchangeArray(array $data)
    {
        $this->SupplierID   = $data['SupplierID'] ?? null;
        $this->SupplierCode = $data['SupplierCode'] ?? null;
        $this->SupplierName = $data['SupplierName'] ?? null;
        $this->Phone        = $data['Phone'] ?? null;
        $this->Email        = $data['Email'] ?? null;
        $this->Address      = $data['Address'] ?? null;
        $this->IsActive     = $data['IsActive'] ?? 1;
        $this->CreatedDate  = $data['CreatedDate'] ?? null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
