<?php

namespace Warehouse\PurchaseItem\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class PurchaseItemTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get all Purchase Order Items
     */
    public function fetchAll()
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select('PurchaseOrderItems');

        $select->columns([
            'PurchaseOrderItemID',
            'PurchaseOrderID',
            'ProductID',
            'Quantity',
            'UnitCost',
            'TotalAmount'
        ]);

        $select->order('PurchaseOrderItemID DESC');

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute();
    }

    /**
     * Get one Purchase Order Item
     */
    public function getPurchaseItem($id)
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select('PurchaseOrderItems');

        $select->columns([
            'PurchaseOrderItemID',
            'PurchaseOrderID',
            'ProductID',
            'Quantity',
            'UnitCost',
            'TotalAmount'
        ]);

        $select->where([
            'PurchaseOrderItemID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        return $result->current();
    }

    /**
     * Get Purchase Orders for dropdown
     */
    public function getPurchaseOrders()
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select('PurchaseOrders');

        $select->columns([
            'PurchaseOrderID',
            'PurchaseOrderNumber'
        ]);

        $select->order('PurchaseOrderID DESC');

        $statement = $sql->prepareStatementForSqlObject($select);

        return $statement->execute();
    }

    /**
     * Get Products for dropdown
     */
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

    /**
     * Insert Purchase Order Item
     *
     * IMPORTANT:
     * TotalAmount is a computed column in SQL Server.
     * Therefore, we DO NOT insert TotalAmount.
     */
    public function savePurchaseItem(PurchaseItem $item)
    {
        $sql = new Sql($this->adapter);

        $insert = $sql->insert('PurchaseOrderItems');

        $insert->values([
            'PurchaseOrderID' => $item->PurchaseOrderID,
            'ProductID'       => $item->ProductID,
            'Quantity'        => $item->Quantity,
            'UnitCost'        => $item->UnitCost
        ]);

        $statement = $sql->prepareStatementForSqlObject($insert);

        return $statement->execute();
    }

    /**
     * Update Purchase Order Item
     *
     * IMPORTANT:
     * TotalAmount is a computed column in SQL Server.
     * Therefore, we DO NOT update TotalAmount.
     */
    public function updatePurchaseItem($id, PurchaseItem $item)
    {
        $sql = new Sql($this->adapter);

        $update = $sql->update('PurchaseOrderItems');

        $update->set([
            'PurchaseOrderID' => $item->PurchaseOrderID,
            'ProductID'       => $item->ProductID,
            'Quantity'        => $item->Quantity,
            'UnitCost'        => $item->UnitCost
        ]);

        $update->where([
            'PurchaseOrderItemID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    /**
     * Delete Purchase Order Item
     */
    public function deletePurchaseItem($id)
    {
        $sql = new Sql($this->adapter);

        $delete = $sql->delete('PurchaseOrderItems');

        $delete->where([
            'PurchaseOrderItemID' => $id
        ]);

        $statement = $sql->prepareStatementForSqlObject($delete);

        return $statement->execute();
    }
}