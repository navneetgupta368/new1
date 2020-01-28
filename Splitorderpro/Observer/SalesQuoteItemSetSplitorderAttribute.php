<?php
namespace Sunarc\Splitorderpro\Observer;

use Magento\Framework\Event\ObserverInterface;

use Magento\Catalog\Model\Product;

class salesQuoteItemSetSplitorderAttribute implements ObserverInterface
{
    protected $_objectManager;
    protected $eavConfig;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Quote\Model\Quote\Item $quote
    ) {
        $this->_objectManager = $objectManager;
        $this->product = $product;
        $this->eavConfig = $eavConfig;
        $this->quote = $quote;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();
        $optionsValue=[];
        $splitAttributeId='';
        $splitAttributeId=$this->_objectManager->create('Sunarc\Splitorderpro\Helper\Data')->getSplitAttrId();
        if ($splitAttributeId !='') {
          //get attribute code by which order will split
            $splitAttributeCode =$this->_objectManager->create('Sunarc\Splitorderpro\Helper\Data')->getSplitAttrCode($splitAttributeId);
          //get productIds on the basis of attribute options
            list($splitAttribute,$attrOptionArray) = $this->_objectManager->create('Sunarc\Splitorderpro\Helper\Data')->getsplitorderCollection();
            foreach ($attrOptionArray as $optionId) {
                $optionsValue[] = $optionId;
            }
            $_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());

            $attr = $_product->getResource()->getAttribute($splitAttributeCode);
            $splitAttributeValue = $_product->getData($splitAttributeCode);
        //$optText = $attr->getSource()->getOptionText($splitAttributeValue);

          
            if ($splitAttributeValue != '' && in_array($splitAttributeValue, $optionsValue)) {
                 $quoteItem->setSplitAttributeValue($splitAttributeValue);
                 $quoteItem->setSplitAttributeCode($splitAttributeCode);
            }
            else {

                //if (!in_array($quoteItem->getProductType(), array('virtual', 'downloadable'))) {
                    $quoteItem->setSplitAttributeValue('remaining');
                    $quoteItem->setSplitAttributeCode($splitAttributeCode);
               // }

            }
        }
    }
}
