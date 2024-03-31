<?php
namespace Abhay\CustomerDiscount\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Abhay\CustomerDiscount\Model\Cache;

class GetCustomDiscount extends \Magento\Framework\App\Action\Action
{
    protected $jsonFactory;
    protected $cache;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->cache = $cache;
    }

    public function execute()
    {
        $customDiscount = json_decode(
            $this->cache->load(\Abhay\CustomerDiscount\Model\Cache\Type\CacheType::TYPE_IDENTIFIER),
            true
        );


        $result = $this->jsonFactory->create();
        return $result->setData(['custom_discount' => $customDiscount['custom_discount'],
            'custom_type' => $customDiscount['custom_type']]
        );
    }
}
