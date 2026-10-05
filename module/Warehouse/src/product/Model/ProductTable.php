<?php

namespace Warehouse\Product\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class ProductTable
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll()
    {
        $sql = new Sql($this->adapter);

      $select = $sql->select('Products');

        $select->where([
            'IsActive' => 1
        ]);

        $select->columns([
            'ProductID',
            'ProductCode',
            'ProductName',
            'CategoryID',
            'UnitPrice',
            'ReorderLevel',
            'IsActive',
            'CreatedDate'
        ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result;
    }
    public function saveProduct(Product $product)
{
    $sql = new Sql($this->adapter);

    $insert = $sql->insert('Products');

    $insert->values([
        'ProductCode'  => $product->ProductCode,
        'ProductName'  => $product->ProductName,
        'CategoryID'   => $product->CategoryID,
        'UnitPrice'    => $product->UnitPrice,
        'ReorderLevel' => $product->ReorderLevel,
        'IsActive'     => 1
    ]);

    $statement = $sql->prepareStatementForSqlObject($insert);

    return $statement->execute();
}
    public function getProduct($id)
{
    $sql = new Sql($this->adapter);

    $select = $sql->select('Products');

    $select->columns([
        'ProductID',
        'ProductCode',
        'ProductName',
        'CategoryID',
        'UnitPrice',
        'ReorderLevel',
        'IsActive',
        'CreatedDate'
    ]);

    $select->where([
        'ProductID' => $id
    ]);

    $statement = $sql->prepareStatementForSqlObject($select);

    $result = $statement->execute();

    $product = $result->current();

    return $product;
}
  public function updateProduct($id, Product $product)
{
    $sql = new Sql($this->adapter);

    $update = $sql->update('Products');

    $update->set([
        'ProductCode'  => $product->ProductCode,
        'ProductName'  => $product->ProductName,
        'CategoryID'   => $product->CategoryID,
        'UnitPrice'    => $product->UnitPrice,
        'ReorderLevel' => $product->ReorderLevel
    ]);

    $update->where([
        'ProductID' => $id
    ]);

    $statement = $sql->prepareStatementForSqlObject($update);

    return $statement->execute();
}  
    public function deleteProduct($id)
{
    $sql = new Sql($this->adapter);

    $update = $sql->update('Products');

    $update->set([
        'IsActive' => 0
    ]);

    $update->where([
        'ProductID' => $id
    ]);

    $statement = $sql->prepareStatementForSqlObject($update);

    return $statement->execute();
}
}