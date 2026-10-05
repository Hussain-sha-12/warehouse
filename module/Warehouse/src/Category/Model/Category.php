<?php

namespace Warehouse\Category\Model;

class Category
{
    public $CategoryID;
    public $CategoryName;
    public $Description;
    public $IsActive;

    public function exchangeArray(array $data)
    {
        $this->CategoryID   = $data['CategoryID'] ?? null;
        $this->CategoryName = $data['CategoryName'] ?? null;
        $this->Description  = $data['Description'] ?? null;
        $this->IsActive     = $data['IsActive'] ?? 1;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}