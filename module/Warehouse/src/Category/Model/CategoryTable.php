<?php

namespace Warehouse\Category\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class CategoryTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll()
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select('Categories');

        $select->where([
            'IsActive' => 1
        ]);

        $select->columns([
            'CategoryID',
            'CategoryName',
            'Description',
            'IsActive'
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute();
    }

    public function getCategory($id)
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select('Categories');

        $select->columns([
            'CategoryID',
            'CategoryName',
            'Description',
            'IsActive'
        ]);

        $select->where([
            'CategoryID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        return $result->current();
    }

    public function saveCategory(Category $category)
    {
        $sql = new Sql($this->adapter);

        $insert = $sql->insert('Categories');

        $insert->values([
            'CategoryName' => $category->CategoryName,
            'Description'  => $category->Description,
            'IsActive'     => 1
        ]);

        $statement = $sql->prepareStatementForSqlObject($insert);

        return $statement->execute();
    }

    public function updateCategory($id, Category $category)
    {
        $sql = new Sql($this->adapter);

        $update = $sql->update('Categories');

        $update->set([
            'CategoryName' => $category->CategoryName,
            'Description'  => $category->Description
        ]);

        $update->where([
            'CategoryID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    public function deleteCategory($id)
{
    $sql = new Sql($this->adapter);

    $update = $sql->update('Categories');

    $update->set([
        'IsActive' => 0
    ]);

    $update->where([
        'CategoryID' => $id
    ]);

    $statement = $sql->prepareStatementForSqlObject($update);

    return $statement->execute();
}
}