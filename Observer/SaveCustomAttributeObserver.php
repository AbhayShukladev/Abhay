<?php

namespace Abhay\CustomerDiscount\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveCustomAttributeObserver implements ObserverInterface
{
    protected $customerRepository;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $customerId = $order->getCustomerId(); // Fetch customer ID directly from the order

            // Retrieve customer attribute value
            $customer = $this->customerRepository->getById($customerId);
            $customDiscountPercentage = $customer->getCustomAttribute('customer_discount')->getValue();
            $customerDiscounttype = $customer->getCustomAttribute('discount_type');

            if (isset($customDiscountPercentage) && isset($customerDiscounttype)) {

                $customerDiscounttypelable = $this->getlable($customerDiscounttype->getValue());
                if ($customerDiscounttypelable == 'percentage') {
                    // Calculate discount based on the attribute value
                    $customDiscount = ($order->getSubtotal() * ($customDiscountPercentage / 100));

                    // Set the custom attribute value to the order
                    $order->setCustomDiscount($customDiscount);

                    return $this;

                }else{
                    // Calculate discount based on the attribute value
                    $customDiscount = $customDiscountPercentage;

                    // Set the custom attribute value to the order
                    $order->setCustomDiscount($customDiscount);

                    return $this;
                }



            }else{
                return 0;
            }
        } catch (\Exception $e) {
            // Handle exception
            return $this;
        }
    }

    private function getlable($optionId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

// Replace 'Magento\Customer\Model\Customer' with your desired class
        $customer = $objectManager->create('Magento\Customer\Model\Customer');

// Replace 'discount_type' with your attribute code
        $attributeCode = 'discount_type';

// Get the attribute model
        $attribute = $customer->getResource()->getAttribute($attributeCode);

// Check if attribute exists
        if ($attribute && $attribute->usesSource()) {
            // Load attribute options
            $attributeOptions = $attribute->getSource()->getAllOptions();

            // Find the label of the option with the given ID
            $label = '';
            foreach ($attributeOptions as $option) {
                if ($option['value'] == $optionId) {
                    $label = $option['label'];
                    break;
                }
            }

            return $label;
        }
    }
}
