<?php
namespace Warehouse\Inventory\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class InventoryTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select('Inventory');

        $select->columns([
            'InventoryID',
            'ProductID',
            'Quantity',
            'ReorderLevel',
            'LastUpdated'
        ]);

        $select->order('InventoryID DESC');

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute();
    }

    public function getInventory($id)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select('Inventory');

        $select->columns([
            'InventoryID',
            'ProductID',
            'Quantity',
            'ReorderLevel',
            'LastUpdated'
        ]);

        $select->where([
            'InventoryID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute()->current();
    }

    public function getProducts()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select('Products');

        $select->columns([
            'ProductID',
            'ProductCode',
            'ProductName'
        ]);

        $select->where([
            'IsActive' => 1
        ]);

        $select->order('ProductName ASC');

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute();
    }

    public function saveInventory(Inventory $inventory)
    {
        $sql = new Sql($this->adapter);
        $insert = $sql->insert('Inventory');

        $insert->values([
            'ProductID' => $inventory->ProductID,
            'Quantity' => $inventory->Quantity,
            'ReorderLevel' => $inventory->ReorderLevel
        ]);

        $statement = $sql->prepareStatementForSqlObject($insert);

        return $statement->execute();
    }

    public function updateInventory($id, Inventory $inventory)
    {
        $sql = new Sql($this->adapter);
        $update = $sql->update('Inventory');

        $update->set([
            'ProductID' => $inventory->ProductID,
            'Quantity' => $inventory->Quantity,
            'ReorderLevel' => $inventory->ReorderLevel
        ]);

        $update->where([
            'InventoryID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    public function deleteInventory($id)
    {
        $sql = new Sql($this->adapter);
        $delete = $sql->delete('Inventory');

        $delete->where([
            'InventoryID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($delete);

        return $statement->execute();
    }
}