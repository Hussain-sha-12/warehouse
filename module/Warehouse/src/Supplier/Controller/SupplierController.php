<?php

namespace Warehouse\Supplier\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\Supplier\Model\Supplier;
use Warehouse\Supplier\Model\SupplierTable;

class SupplierController extends AbstractActionController
{
    private $supplierTable;

    public function __construct(SupplierTable $supplierTable)
    {
        $this->supplierTable = $supplierTable;
    }

    public function indexAction()
    {
        return new ViewModel([
            'suppliers' => $this->supplierTable->fetchAll()
        ]);
    }

    public function createAction()
    {
        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $supplier = new Supplier();

            $supplier->SupplierCode =
                trim($request->getPost('SupplierCode', ''));

            $supplier->SupplierName =
                trim($request->getPost('SupplierName', ''));

            $supplier->Phone =
                trim($request->getPost('Phone', ''));

            $supplier->Email =
                trim($request->getPost('Email', ''));

            $supplier->Address =
                trim($request->getPost('Address', ''));

            if ($supplier->SupplierCode === '') {

                $error = 'Supplier code is required.';

            } elseif ($supplier->SupplierName === '') {

                $error = 'Supplier name is required.';

            } else {

                try {

                    $this->supplierTable
                        ->saveSupplier($supplier);

                    return $this->redirect()
                        ->toRoute('supplier');

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

        $supplier =
            $this->supplierTable->getSupplier($id);

        if (!$supplier) {
            return $this->redirect()
                ->toRoute('supplier');
        }

        $request = $this->getRequest();
        $error = null;

        if ($request->isPost()) {

            $model = new Supplier();

            $model->SupplierCode =
                trim($request->getPost('SupplierCode', ''));

            $model->SupplierName =
                trim($request->getPost('SupplierName', ''));

            $model->Phone =
                trim($request->getPost('Phone', ''));

            $model->Email =
                trim($request->getPost('Email', ''));

            $model->Address =
                trim($request->getPost('Address', ''));

            $model->IsActive =
                (int) $request->getPost('IsActive', 1);

            if ($model->SupplierCode === '') {

                $error = 'Supplier code is required.';

            } elseif ($model->SupplierName === '') {

                $error = 'Supplier name is required.';

            } else {

                try {

                    $this->supplierTable
                        ->updateSupplier($id, $model);

                    return $this->redirect()
                        ->toRoute('supplier');

                } catch (\Exception $e) {

                    $error = $e->getMessage();
                }
            }

            $supplier = array_merge(
                $supplier,
                $model->getArrayCopy()
            );
        }

        return new ViewModel([
            'supplier' => $supplier,
            'error'    => $error
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()
            ->fromRoute('id', 0);

        if ($id > 0) {
            $this->supplierTable
                ->deleteSupplier($id);
        }

        return $this->redirect()
            ->toRoute('supplier');
    }
}
