<?php

namespace Warehouse\User\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class UserTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getUserByUsername($username)
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select('Users');

        $select->columns([
            'UserID',
            'Username',
            'PasswordHash',
            'RoleID',
            'IsActive',
            'CreatedDate'
        ]);

        $select->where([
            'Username' => $username,
            'IsActive' => 1
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute()->current();
    }
}