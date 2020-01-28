<?php
/**
 * Sunarc_Splitorderpro extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the SunArc Technologies License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://sunarctechnologies.com/end-user-agreement/
 *
 * @category  Sunarc
 * @package   Sunarc_Splitorderpro
 * @copyright Copyright (c) 2017
 * @license
 */
namespace Sunarc\Splitorderpro\Model\Splitattr\Source;

class AttrValue implements \Magento\Framework\Option\ArrayInterface
{
    const ONE_ON_EACH_ROW = 1;
    protected $_objectManager;
    protected $splitattrCollectionFactory;
    protected $eavConfig;
    protected $_request;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Request\Http $request,
        \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\CollectionFactory $splitattrCollectionFactory
    ) {

        $this->_objectManager = $objectmanager;
        $this->splitattrCollectionFactory = $splitattrCollectionFactory;
        $this->_request = $request;
        $this->eavConfig = $eavConfig;
    }

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsExists=[];
        $entityId=$this->_request->getParam('splitattr_id');
        if ($entityId) {
            $splitFactory = $this->splitattrCollectionFactory->create()->addFieldToSelect('split_order_attr')
                ->addFieldToSelect('split_order_attr')
                ->addFieldToFilter('splitattr_id', ['eq' => $entityId])
                ->load();
            foreach ($splitFactory as $val) {
                $attributeId=$val->getSplitOrderAttr();
            }
            $eavModel = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
            $attr = $eavModel->load($attributeId);
            $attributeCode = $eavModel->getAttributeCode();
            $source = $this->eavConfig->getAttribute("catalog_product", $attributeCode);
            $allOptions=$source->getSource()->getAllOptions();
            foreach ($allOptions as $option) {
                $optionsExists[] =['value' => $source->getSource()->getOptionId($option['label']), 'label' => $option['label']];
            }
        }

        return $optionsExists;
    }
}
