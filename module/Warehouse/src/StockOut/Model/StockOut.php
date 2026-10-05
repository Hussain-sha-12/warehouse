<?php

namespace Warehouse\StockOut\Model;

class StockOut
{
    public $ProductID;
    public $Quantity;

    public function exchangeArray(array $data)
    {
        $this->ProductID = $data['ProductID'] ?? null;
        $this->Quantity  = $data['Quantity'] ?? 0;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}