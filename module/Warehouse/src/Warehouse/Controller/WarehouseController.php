<?php

namespace Warehouse\Warehouse\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\Warehouse\Model\Warehouse;
use Warehouse\Warehouse\Model\WarehouseTable;

class WarehouseController extends AbstractActionController
{
    private $warehouseTable;

    public function __construct(WarehouseTable $warehouseTable)
    {
        $this->warehouseTable = $warehouseTable;
    }

    public function indexAction()
    {
        return new ViewModel([
            'warehouses' =>
                $this->warehouseTable->fetchAll()
        ]);
    }

    public function createAction()
    {
        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $warehouse = new Warehouse();

            $warehouse->WarehouseCode =
                trim($request->getPost('WarehouseCode', ''));

            $warehouse->WarehouseName =
                trim($request->getPost('WarehouseName', ''));

            $warehouse->Location =
                trim($request->getPost('Location', ''));

            if ($warehouse->WarehouseCode === '') {

                $error = 'Warehouse code is required.';

            } elseif ($warehouse->WarehouseName === '') {

                $error = 'Warehouse name is required.';

            } else {

                try {

                    $this->warehouseTable
                        ->saveWarehouse($warehouse);

                    return $this->redirect()
                        ->toRoute('warehouses');

                } catch (\Exception $e) {

                    $error = $e->getMessage();
                }
            }
        }

        return new ViewModel([
            'error' => $error
        ]);
    }

    public function editAction()
    {
        $id = (int) $this->params()
            ->fromRoute('id', 0);

        $warehouse =
            $this->warehouseTable
                ->getWarehouse($id);

        if (!$warehouse) {
            return $this->redirect()
                ->toRoute('warehouses');
        }

        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $model = new Warehouse();

            $model->WarehouseCode =
                trim($request->getPost('WarehouseCode', ''));

            $model->WarehouseName =
                trim($request->getPost('WarehouseName', ''));

            $model->Location =
                trim($request->getPost('Location', ''));

            $model->IsActive =
                (int) $request->getPost(
                    'IsActive',
                    1
                );

            if ($model->WarehouseCode === '') {

                $error =
                    'Warehouse code is required.';

            } elseif ($model->WarehouseName === '') {

                $error =
                    'Warehouse name is required.';

            } else {

                try {

                    $this->warehouseTable
                        ->updateWarehouse(
                            $id,
                            $model
                        );

                    return $this->redirect()
                        ->toRoute('warehouses');

                } catch (\Exception $e) {

                    $error = $e->getMessage();
                }
            }

            $warehouse = array_merge(
                $warehouse,
                $model->getArrayCopy()
            );
        }

        return new ViewModel([
            'warehouse' => $warehouse,
            'error'     => $error
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()
            ->fromRoute('id', 0);

        if ($id > 0) {

            $this->warehouseTable
                ->deleteWarehouse($id);
        }

        return $this->redirect()
            ->toRoute('warehouses');
    }
}