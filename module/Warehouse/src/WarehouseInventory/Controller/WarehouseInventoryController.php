<?php

namespace Warehouse\WarehouseInventory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\WarehouseInventory\Model\WarehouseInventoryTable;

class WarehouseInventoryController
    extends AbstractActionController
{
    private $warehouseInventoryTable;

    public function __construct(
        WarehouseInventoryTable $warehouseInventoryTable
    ) {
        $this->warehouseInventoryTable =
            $warehouseInventoryTable;
    }

    public function indexAction()
    {
        $inventory =
            iterator_to_array(
                $this->warehouseInventoryTable
                    ->getInventory()
            );

        return new ViewModel([
            'inventory' => $inventory
        ]);
    }
}
