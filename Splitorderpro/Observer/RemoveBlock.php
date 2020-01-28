<?php

namespace Sunarc\Splitorderpro\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Sunarc\Splitorderpro\Block\Success;
use Sunarc\Splitorderpro\Helper\Data;

class RemoveBlock implements ObserverInterface
{
    protected $_scopeConfig;
    protected $successBlock;
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Success $successBlock,
        Data $helper
    ) {
    
        $this->_scopeConfig = $scopeConfig;
        $this->successBlock = $successBlock;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        $checkOrder=0;
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('checkout.success');
        if ($block) {
            $remove = $this->_scopeConfig->getValue(
                'splitorderpro/general/enable',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $checkCondition = $this->helper->getConditionEnabled();
            $orderIds=$this->successBlock->getOrders();
            $isSplitOrder=$this->successBlock->isSplitOrder();
            $checkOrder=count($orderIds);
            if ($isSplitOrder) {
                if ($checkCondition != 2) {
                    if ($remove) {
                        $layout->unsetElement('checkout.success');
                    }
                } else {
                    if ($checkOrder >1) {
                        if ($remove) {
                            $layout->unsetElement('checkout.success');
                        }
                    } else {
                        $layout->unsetElement('sunarc.order.success');
                        $layout->setBlock('sunarc.order.success', 'checkout.success');
                    }
                }
            } else {
                $layout->unsetElement('sunarc.order.success');
                $layout->setBlock('sunarc.order.success', 'checkout.success');
            }
        }
    }
}
