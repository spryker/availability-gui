<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityGui\Communication\Form;

use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @method \Spryker\Zed\AvailabilityGui\Communication\AvailabilityGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\AvailabilityGui\AvailabilityGuiConfig getConfig()
 * @method \Spryker\Zed\AvailabilityGui\Persistence\AvailabilityGuiRepositoryInterface getRepository()
 */
class StockSubForm extends AbstractType
{
    public const FIELD_QUANTITY = 'quantity';
    public const FIELD_STOCK_TYPE = 'stockType';
    public const FIELD_IS_NEVER_OUT_OF_STOCK = 'is_never_out_of_stock';

    protected const DECIMAL_QUANTITY_VALIDATION_PATTERN = '/^\d{1,10}(\.\d{1,20})?$/';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addQuantityField($builder)
            ->addStockTypeField($builder)
            ->addIsNeverOutOfStockCheckbox($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addQuantityField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_QUANTITY, NumberType::class, [
            'label' => 'Quantity',
            'attr' => ['min' => PHP_INT_MIN, 'max' => PHP_INT_MAX],
            'html5' => true,
            'constraints' => [
                new Type('numeric'),
                new Regex([
                    'pattern' => static::DECIMAL_QUANTITY_VALIDATION_PATTERN,
                ]),
            ],
        ]);

        $builder->get(static::FIELD_QUANTITY)
            ->resetViewTransformers();

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addStockTypeField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_STOCK_TYPE, TextType::class, [
            'label' => 'Stock Type',
            'disabled' => true,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addIsNeverOutOfStockCheckbox(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_IS_NEVER_OUT_OF_STOCK, CheckboxType::class, [
            'label' => 'Never out of stock',
            'required' => false,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     *
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Generated\Shared\Transfer\StockProductTransfer $stockProductTransfer */
        $stockProductTransfer = $form->getViewData();

        $mapping = $this->getFactory()->getStockFacade()->getWarehouseToStoreMapping();
        if (isset($mapping[$stockProductTransfer->getStockType()])) {
            $view->vars['available_in_stores'] = $mapping[$stockProductTransfer->getStockType()];
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'stock_form';
    }

    /**
     * @deprecated Use {@link getBlockPrefix()} instead.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
