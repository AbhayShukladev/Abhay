<?php
namespace Abhay\CustomerDiscount\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class AddCustomDiscountToOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    protected $resultJsonFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepository,
        SessionManagerInterface $sessionManager
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->sessionManager = $sessionManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $customerId = $quote->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $customDiscountPercentage = $customer->getCustomAttribute('customer_discount')->getValue();
        $customerDiscounttype = $customer->getCustomAttribute('discount_type');
        if (isset($customDiscountPercentage) && isset($customerDiscounttype)) {
            $customerDiscounttypelable = $this->getlable($customerDiscounttype->getValue());
            if ($customerDiscounttypelable == 'percentage') {

                // Calculate discount based on the attribute value
                $customDiscount = ($order->getSubtotal() * ($customDiscountPercentage / 100));

                // Store custom discount in session variable
                $this->sessionManager->start();
                $this->sessionManager->setCustomDiscount($customDiscount);
            }else{
                // Calculate discount based on the attribute value
                $customDiscount = $customDiscountPercentage;

                // Store custom discount in session variable
                $this->sessionManager->start();
                $this->sessionManager->setCustomDiscount($customDiscount);


            }



        // Return JSON response
        $result = $this->resultJsonFactory->create();
        return $result->setData(['custom_discount' => $customDiscount]);

        // Fetch custom discount from quote
        // $customDiscount = 50;

        // Add custom discount to order totals
        if (!empty($customDiscount)) {
            $order->setCustomDiscount($customDiscount);
            $order->setBaseCustomDiscount($customDiscount); // Assuming you have a base custom discount
            $order->setGrandTotal($order->getGrandTotal());
            $order->setBaseGrandTotal($order->getBaseGrandTotal());
        }
    }else{
            $result = $this->resultJsonFactory->create();
            return $result->setData(['custom_discount' => 0]);
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
