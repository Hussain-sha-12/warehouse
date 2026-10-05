<?php

namespace Warehouse\Inventory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\Inventory\Model\Inventory;
use Warehouse\Inventory\Model\InventoryTable;

class InventoryController extends AbstractActionController
{
    private $inventoryTable;

    public function __construct(InventoryTable $inventoryTable)
    {
        $this->inventoryTable = $inventoryTable;
    }

    public function indexAction()
    {
        $inventories = $this->inventoryTable->fetchAll();

        return new ViewModel([
            'inventories' => $inventories
        ]);
    }

    public function addAction()
    {
        $request = $this->getRequest();

        $products = $this->inventoryTable->getProducts();

        if ($request->isPost()) {

            $data = $request->getPost()->toArray();

            $inventory = new Inventory();

            $inventory->exchangeArray([
                'ProductID'    => $data['ProductID'] ?? null,
                'Quantity'     => $data['Quantity'] ?? 0,
                'ReorderLevel' => $data['ReorderLevel'] ?? 0
            ]);

            $this->inventoryTable->saveInventory($inventory);

            return $this->redirect()->toRoute('inventory');
        }

        return new ViewModel([
            'products' => $products
        ]);
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if ($id <= 0) {
            return $this->redirect()->toRoute('inventory');
        }

        $inventory = $this->inventoryTable->getInventory($id);

        if (!$inventory) {
            return $this->redirect()->toRoute('inventory');
        }

        $products = $this->inventoryTable->getProducts();

        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = $request->getPost()->toArray();

            $updatedInventory = new Inventory();

            $updatedInventory->exchangeArray([
                'ProductID'    => $data['ProductID'] ?? null,
                'Quantity'     => $data['Quantity'] ?? 0,
                'ReorderLevel' => $data['ReorderLevel'] ?? 0
            ]);

            $this->inventoryTable->updateInventory(
                $id,
                $updatedInventory
            );

            return $this->redirect()->toRoute('inventory');
        }

        return new ViewModel([
            'inventory' => $inventory,
            'products'  => $products,
            'id'        => $id
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if ($id > 0) {
            $this->inventoryTable->deleteInventory($id);
        }

        return $this->redirect()->toRoute('inventory');
    }
}