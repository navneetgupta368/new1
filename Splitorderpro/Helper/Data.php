<?php
/**
 * Sunarc_Splitorderpro
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Sunarc_Splitorderpro
 *
 * @category Sunarc_Splitorderpro
 * @package Sunarc_Splitorderpro
 * @copyright Copyright (c) 2014 Zowta LLC (http://www.sunarctechnologies.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author Sunarc_Splitorderpro Team support@sunarctechnologies.com
 *
 */

namespace Sunarc\Splitorderpro\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\CollectionFactory;

class Data extends AbstractHelper
{

    const CONFIG_PATH_MODULE_ENABLED = 'splitorderpro/general/enable';
    const CONFIG_PATH_CONDITION_ENABLED = 'splitorderpro/general/selectoption';
    //const CONFIG_PATH_WEIGHT_CONDITION = 'splitorderpro/general/weightoption';


    protected $_objectManager;
    protected $scopeConfig;
    protected $eavConfig;
    protected $block;
    protected $splitattrCollectionFactory;
    protected $checkoutSession;
    protected $authSession;
    protected $orderCollectionFactory;
    protected $userFactory;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\User\Model\UserFactory $userFactory,
        CollectionFactory $splitattrCollectionFactory
    ) {
    
        $this->_objectManager = $objectmanager;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->eavConfig = $eavConfig;
        $this->authSession = $authSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->userFactory = $userFactory;
        $this->splitattrCollectionFactory = $splitattrCollectionFactory;
    }

    /*
     * check if module enabled
     */
    public function getConfigModuleEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_MODULE_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /*
     * check enabled conditions
     */
    public function getConditionEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CONDITION_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

   /* public function getweight()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_WEIGHT_CONDITION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }*/

    /**
     * used if order splits on the basis of 'split order if attribute exists'
     * Check the attribute exist in order or not.
     * @access public
     *
     */

    public function checkAttributeExist()
    {
        if ($this->getsplitorderproCollection()!=0) {
            list($splitAttribute, $attrOptionArray) = $this->getsplitorderproCollection();
            $attributeData = [];
            $attributeExist = 0;
            $splitAttributeCode = $attributeCode = $this->getSplitAttrCode($splitAttribute);
            foreach ($this->checkoutSession->getQuote()->getAllItems() as $item) {
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());
                $optText = $product->getAttributeText($splitAttributeCode);
                if (is_array($optText) && $optText) {
                    foreach ($optText as $value) {
                        $attr = $product->getResource()->getAttribute($splitAttributeCode);
                        $attributeData[] = $attr->getSource()->getOptionId($value);
                    }
                } else {
                    $attr = $product->getResource()->getAttribute($splitAttributeCode);
                    $attributeData[] = $attr->getSource()->getOptionId($optText);
                }
            }

            $count = count(array_intersect($attrOptionArray, $attributeData));
            if ($count) {
                $attributeExist = 1;
            }
            return $attributeExist;
        } else {
            return 0;
        }
    }

    /**
     * used if order splits on the basis of 'split order on basis of attribute'
     * Return Product Ids on the basis of their attributes
     * Check the attribute exist in order or not.
     * @access public
     * @return array
     */
    public function splitAttribute()
    {
        $optionText=[];
        $productIDs=[];
        if ($this->getsplitorderproCollection()!=0) {
            list($splitAttribute, $attrOptionArray) = $this->getsplitorderproCollection();
            $attributeCode = $this->getSplitAttrCode($splitAttribute);
            foreach ($this->checkoutSession->getQuote()->getAllItems() as $item) {
                //get productId's array getting option value from each product
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());
                if ($product->getTypeId() == 'virtual') {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $product_new = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($item->getProduct()->getId());
                    $optText = $product->getAttributeText($attributeCode);
                    if (is_array($optText) && $optText) {
                        $check_arr_i = 0;
                        foreach ($optText as $value) {
                            if ($check_arr_i == 0) {
                                $productIDs[$value][] = $product_new[0];
                                $check_arr_i = 1;
                            }
                        }
                    } else {
                        $productIDs[$product->getAttributeText($attributeCode)][] = $product_new[0];
                    }
                } elseif ($product->getTypeId() != 'configurable') {
                    $optText = $product->getAttributeText($attributeCode);
                    if (is_array($optText) && $optText) {
                        $check_arr_i = 0;
                        foreach ($optText as $value) {
                            if ($check_arr_i == 0) {
                                $productIDs[$value][] = $item->getProduct()->getId();
                                $check_arr_i = 1;
                            }
                        }
                    } else {
                        $productIDs[$product->getAttributeText($attributeCode)][] = $item->getProduct()->getId();
                    }
                }
            }


            return $productIDs;
        } else {
            $new_array= [];
            return $new_array;
        }
    }
    /**
     * used if order splits on the basis of 'split order on basis of attribute'
     * Return Attribute Ids of selected attribute from split order by attribute grid
     * @access public
     * @return array
     */
    public function getSplitAttrId()
    {
        //get attribute ids by which order will split
        list($splitAttribute,$attrOptionArray) = $this->getsplitorderproCollection();
        return $splitAttribute;
    }
    /**
     * used if order splits on the basis of 'split order on basis of attribute'
     * Return split order attribute options array based on selected attribute from split order by attribute grid
     * @access public
     * @return array
     */
    public function splitoptionArr()
    {
        list($splitAttribute,$attrOptionArray) = $this->getsplitorderproCollection();
        $attributeCode = $this->getSplitAttrCode($splitAttribute);
        $entityType = 'catalog_product';
        //get attribute details from attribute code
        $attributeDetails = $this->eavConfig->getAttribute("catalog_product", $attributeCode);
        foreach ($attrOptionArray as $optionId) {
            //get attribute option's label from option id
            $optionText[] = $attributeDetails->getSource()->getOptionText($optionId);
        }

        return $optionText;
    }

    public function getsplitorderproCollection()
    {
        $splitFactory = $this->splitattrCollectionFactory->create()->addFieldToSelect('split_order_attr')
            ->addFieldToSelect('attr_value')
            ->setOrder('priority', 'ASC')
            ->setOrder('updated_at', 'DESC')
            ->getFirstItem();

        $storeData=$splitFactory->getData();
        if (!empty($storeData)) {
            $latestSplitAttr = $splitFactory->getFirstItem();
            $attributeOptions = $storeData['attr_value'];

            $attrOptionArray = explode(',', $attributeOptions);
            $splitAttribute = $storeData['split_order_attr'];
            return [$splitAttribute, $attrOptionArray];
        } else {
            return '';
        }
    }

    /**
     * @param $splitAttribute
     * @return mixed
     */
    public function getSplitAttrCode($splitAttribute)
    {
        $eavModel = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
        $attr = $eavModel->load($splitAttribute);
        $attributeCode = $eavModel->getAttributeCode();
        return $attributeCode;
    }
}
