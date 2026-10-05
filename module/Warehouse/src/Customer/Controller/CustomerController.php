<?php

namespace Warehouse\Customer\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\Customer\Model\Customer;
use Warehouse\Customer\Model\CustomerTable;

class CustomerController extends AbstractActionController
{
    private $customerTable;

    public function __construct(CustomerTable $customerTable)
    {
        $this->customerTable = $customerTable;
    }

    public function indexAction()
    {
        return new ViewModel([
            'customers' => $this->customerTable->fetchAll()
        ]);
    }

    public function createAction()
    {
        $error = null;
        $request = $this->getRequest();

        if ($request->isPost()) {

            $customer = new Customer();

            $customer->CustomerCode =
                trim($request->getPost('CustomerCode', ''));

            $customer->CustomerName =
                trim($request->getPost('CustomerName', ''));

            $customer->Phone =
                trim($request->getPost('Phone', ''));

            $customer->Email =
                trim($request->getPost('Email', ''));

            $customer->Address =
                trim($request->getPost('Address', ''));

            if ($customer->CustomerCode === '') {
                $error = 'Customer code is required.';
            } elseif ($customer->CustomerName === '') {
                $error = 'Customer name is required.';
            } else {
                try {
                    $this->customerTable
                        ->saveCustomer($customer);

                    return $this->redirect()
                        ->toRoute('customers');
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

        $customer =
            $this->customerTable->getCustomer($id);

        if (!$customer) {
            return $this->redirect()
                ->toRoute('customers');
        }

        $error = null;
        $request = $this->getRequest();

        if ($request->isPost()) {

            $customerModel = new Customer();

            $customerModel->CustomerCode =
                trim($request->getPost('CustomerCode', ''));

            $customerModel->CustomerName =
                trim($request->getPost('CustomerName', ''));

            $customerModel->Phone =
                trim($request->getPost('Phone', ''));

            $customerModel->Email =
                trim($request->getPost('Email', ''));

            $customerModel->Address =
                trim($request->getPost('Address', ''));

            $customerModel->IsActive =
                (int) $request->getPost('IsActive', 1);

            if ($customerModel->CustomerCode === '') {
                $error = 'Customer code is required.';
            } elseif ($customerModel->CustomerName === '') {
                $error = 'Customer name is required.';
            } else {
                try {
                    $this->customerTable
                        ->updateCustomer(
                            $id,
                            $customerModel
                        );

                    return $this->redirect()
                        ->toRoute('customers');
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }

            $customer = array_merge(
                $customer,
                $customerModel->getArrayCopy()
            );
        }

        return new ViewModel([
            'customer' => $customer,
            'error'    => $error
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()
            ->fromRoute('id', 0);

        if ($id > 0) {
            $this->customerTable
                ->deleteCustomer($id);
        }

        return $this->redirect()
            ->toRoute('customers');
    }
}