<?php

namespace Warehouse\Category\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Warehouse\Category\Model\CategoryTable;
use Warehouse\Category\Model\Category;

class CategoryController extends AbstractActionController
{
    private $categoryTable;

    public function __construct(CategoryTable $categoryTable)
    {
        $this->categoryTable = $categoryTable;
    }

    public function indexAction()
    {
        $categories = $this->categoryTable->fetchAll();

        return new ViewModel([
            'categories' => $categories
        ]);
    }

    public function addAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            $category = new Category();
            $category->exchangeArray($data);

            $this->categoryTable->saveCategory($category);

            return $this->redirect()->toRoute('category');
        }

        return new ViewModel();
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if ($id <= 0) {
            return $this->redirect()->toRoute('category');
        }

        $category = $this->categoryTable->getCategory($id);

        if (!$category) {
            return $this->redirect()->toRoute('category');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            $updatedCategory = new Category();
            $updatedCategory->exchangeArray($data);

            $this->categoryTable->updateCategory($id, $updatedCategory);

            return $this->redirect()->toRoute('category');
        }

        return new ViewModel([
            'category' => $category,
            'id' => $id
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if ($id <= 0) {
            return $this->redirect()->toRoute('category');
        }

        $this->categoryTable->deleteCategory($id);

        return $this->redirect()->toRoute('category');
    }
}