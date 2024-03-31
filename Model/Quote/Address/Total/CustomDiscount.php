<?php
namespace Abhay\CustomerDiscount\Model\Quote\Address\Total;

use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->customerRepository = $customerRepository;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        // Retrieve customer attribute value
        $customerId = $quote->getCustomerId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customDiscountPercentage = $customer->getCustomAttribute('customer_discount')->getValue();

            // Calculate discount based on the attribute value
            $customDiscount = -($total->getSubtotal() * ($customDiscountPercentage / 100));

            // Apply discount
            $total->addTotalAmount('customdiscount', $customDiscount);
            $total->addBaseTotalAmount('customdiscount', $customDiscount);
            $quote->setCustomDiscount($customDiscount);
        }

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $customerId = $quote->getCustomerId();
        $customDiscountValue = 0;
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customDiscountPercentage = $customer->getCustomAttribute('customer_discount')->getValue();
            // Calculate custom discount value based on the attribute
            $customDiscountValue = ($total->getSubtotal() * ($customDiscountPercentage / 100));
        }

        return [
            'code' => 'customdiscount',
            'title' => $this->getLabel(),
            'value' => $customDiscountValue
        ];
    }


    /**
     * get label
     * @return string
     */
    public function getLabel()
    {
        return __('Custom Discount');
    }
}
