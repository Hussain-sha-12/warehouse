<?php

namespace Warehouse\Customer\Model;

class Customer
{
    public $CustomerID;
    public $CustomerCode;
    public $CustomerName;
    public $Phone;
    public $Email;
    public $Address;
    public $IsActive;
    public $CreatedDate;

    public function exchangeArray(array $data)
    {
        $this->CustomerID   = $data['CustomerID'] ?? null;
        $this->CustomerCode = $data['CustomerCode'] ?? null;
        $this->CustomerName = $data['CustomerName'] ?? null;
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