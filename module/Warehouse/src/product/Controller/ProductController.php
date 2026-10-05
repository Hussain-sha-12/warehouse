<?php

namespace Warehouse\Product\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\Product\Model\ProductTable;

class ProductController extends AbstractActionController
{
    private $productTable;

    public function __construct(ProductTable $productTable)
    {
        $this->productTable = $productTable;
    }

    public function indexAction()
    {
        $products = $this->productTable->fetchAll();

        return new ViewModel([
            'products' => $products
        ]);
    }
    public function addAction()
{
    $form = new \Warehouse\Product\Form\ProductForm();

    $request = $this->getRequest();

    if ($request->isPost()) {

        $data = $request->getPost()->toArray();

        $product = new \Warehouse\Product\Model\Product();

        $product->exchangeArray($data);

        $this->productTable->saveProduct($product);

        return $this->redirect()->toRoute('product');
    }

    return new ViewModel([
        'form' => $form
    ]);
}
  public function editAction()
{
    $id = (int) $this->params()->fromRoute('id', 0);

    if ($id <= 0) {
        return $this->redirect()->toRoute('product');
    }

    $product = $this->productTable->getProduct($id);

    if (!$product) {
        return $this->redirect()->toRoute('product');
    }

    $form = new \Warehouse\Product\Form\ProductForm();

    $form->setData([
        'ProductCode'  => $product['ProductCode'],
        'ProductName'  => $product['ProductName'],
        'CategoryID'   => $product['CategoryID'],
        'UnitPrice'    => $product['UnitPrice'],
        'ReorderLevel' => $product['ReorderLevel'],
    ]);

    $request = $this->getRequest();

    if ($request->isPost()) {

        $data = $request->getPost()->toArray();

        $updatedProduct = new \Warehouse\Product\Model\Product();

        $updatedProduct->exchangeArray($data);

        $this->productTable->updateProduct($id, $updatedProduct);

        return $this->redirect()->toRoute('product');
    }

    return new ViewModel([
        'form' => $form,
        'product' => $product,
        'id' => $id
    ]);
}
    public function deleteAction()
{
    $id = (int) $this->params()->fromRoute('id', 0);

    if ($id <= 0) {
        return $this->redirect()->toRoute('product');
    }

    $this->productTable->deleteProduct($id);

    return $this->redirect()->toRoute('product');
}
}