<?php

namespace Sunarc\Splitorderpro\Block;

use Magento\Sales\Model\Order\Address;

class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    public $scopeConfig;
    const XML_PATH_ORDER_SUCCESS = 'splitorderpro/general/enable';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function getOrders()
    {
        return $this->checkoutSession->getSeparetedOrderIds();
    }
    
    public function isSplitOrder()
    {
        return $this->checkoutSession->getSplitOrder();
    }

    public function isEnabled()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_ORDER_SUCCESS, $storeScope);
    }
}
