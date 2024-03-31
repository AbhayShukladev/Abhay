<?php
namespace Abhay\CustomerDiscount\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class CustomDiscount extends Template
{
    protected $_template = 'order/view/custom_discount.phtml';

    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    public function getCustomDiscount()
    {
        $order = $this->getOrder();
        return $order->getData('custom_discount');
    }
}
