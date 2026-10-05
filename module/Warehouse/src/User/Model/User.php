<?php

namespace Warehouse\User\Model;

class User
{
    public $UserID;
    public $Username;
    public $PasswordHash;
    public $RoleID;
    public $IsActive;
    public $CreatedDate;

    public function exchangeArray(array $data)
    {
        $this->UserID       = $data['UserID'] ?? null;
        $this->Username     = $data['Username'] ?? null;
        $this->PasswordHash = $data['PasswordHash'] ?? null;
        $this->RoleID       = $data['RoleID'] ?? null;
        $this->IsActive     = $data['IsActive'] ?? 0;
        $this->CreatedDate  = $data['CreatedDate'] ?? null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}