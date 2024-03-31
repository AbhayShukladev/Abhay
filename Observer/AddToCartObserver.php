<?php
namespace Abhay\CustomerDiscount\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class AddToCartObserver implements ObserverInterface
{
    protected $customerSession;
    protected $checkoutSession;
    protected $cache;
    protected $customerRepository;

    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->cache = $cache;
        $this->customerRepository = $customerRepository;
    }

    public function execute(Observer $observer)
    {
        $data =[];
        // Get the current customer ID
        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            try {
                // Load customer by ID
                $customer = $this->customerRepository->getById($customerId);

                // Get the custom attribute value
                $customerDiscount = $customer->getCustomAttribute('customer_discount');
                $customerDiscounttype = $customer->getCustomAttribute('discount_type');

                $customerDiscounttypelable= $this->getlable($customerDiscounttype->getValue());

                if (isset($customerDiscount) && isset($customerDiscounttypelable)) {

                if ($customerDiscounttypelable === 'percentage'){

                    $data=[
                        'custom_discount' => $customerDiscount->getValue(),
                        'custom_type' => $customerDiscounttypelable
                    ];
                    // Store the custom attribute value in cache
                    $this->cache->save(
                        json_encode($data),
                        \Abhay\CustomerDiscount\Model\Cache\Type\CacheType::TYPE_IDENTIFIER,
                        [\Abhay\CustomerDiscount\Model\Cache\Type\CacheType::CACHE_TAG],
                        86400
                    );
                }else{
                    $data=[
                        'custom_discount' => $customerDiscount->getValue(),
                        'custom_type' => 'kgu'
                    ];
                    // Store the custom attribute value in cache
                    $this->cache->save(
                        json_encode($data),
                        \Abhay\CustomerDiscount\Model\Cache\Type\CacheType::TYPE_IDENTIFIER,
                        [\Abhay\CustomerDiscount\Model\Cache\Type\CacheType::CACHE_TAG],
                        86400
                    );
                }

                }else{
                    $this->cache->save(
                        json_encode(''),
                        \Abhay\CustomerDiscount\Model\Cache\Type\CacheType::TYPE_IDENTIFIER,
                        [\Abhay\CustomerDiscount\Model\Cache\Type\CacheType::CACHE_TAG],
                        86400
                    );
                }
            } catch (\Exception $e) {
                // Handle exception
                return;
            }
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
