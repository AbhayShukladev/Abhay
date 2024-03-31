<?php
namespace Abhay\CustomerDiscount\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerDiscountAttributesPatch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * AddCustomerDiscountAttributesPatch constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'customer_discount',
            [
                'type' => 'decimal',
                'label' => 'Customer Discount',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'system' => false,
                'position' => 100
            ]
        );

        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $salesSetup->addAttribute(
            Order::ENTITY,
            'order_discount',
            [
                'type' => 'decimal',
                'label' => 'Order Discount',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'system' => false,
                'position' => 100
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'discount_type',
            [
                'type' => 'varchar',
                'label' => 'Discount Type',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'option' => ['values' => ['percentage' => 'percentage', 'fixed' => 'fixed']],
                'required' => false,
                'visible' => true,
                'system' => false,
                'position' => 110
            ]
        );


        $customerDiscountType = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'discount_type');
        $customerDiscount = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'customer_discount');


        $data = [];

        if ($customerDiscount->getAttributeId()) {
            $usedInForms = ['adminhtml_customer', 'customer_account_create'];

            foreach ($usedInForms as $formCode) {
                $data[] = ['form_code' => $formCode, 'attribute_id' => $customerDiscount->getAttributeId()];
            }
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable('customer_form_attribute'),
                $data
            );
        }

        if ($customerDiscountType) {
            $customerDiscountType->setData(
                'used_in_forms',
                ['adminhtml_customer', 'customer_account_create']
            );
            $customerDiscountType->save();
        }
        $this->moduleDataSetup->endSetup();
    }
}
