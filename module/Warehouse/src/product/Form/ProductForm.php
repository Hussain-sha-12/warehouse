<?php

namespace Warehouse\Product\Form;

use Zend\Form\Form;

class ProductForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('product');

        $this->add([
            'name' => 'ProductCode',
            'type' => 'text',
            'options' => [
                'label' => 'Product Code',
            ],
        ]);

        $this->add([
            'name' => 'ProductName',
            'type' => 'text',
            'options' => [
                'label' => 'Product Name',
            ],
        ]);

        $this->add([
            'name' => 'CategoryID',
            'type' => 'text',
            'options' => [
                'label' => 'Category ID',
            ],
        ]);

        $this->add([
            'name' => 'UnitPrice',
            'type' => 'text',
            'options' => [
                'label' => 'Unit Price',
            ],
        ]);

        $this->add([
            'name' => 'ReorderLevel',
            'type' => 'text',
            'options' => [
                'label' => 'Reorder Level',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Save Changes',
            ],
        ]);
    }
}