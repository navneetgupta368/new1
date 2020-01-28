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

class SplitOrderAttr implements \Magento\Framework\Option\ArrayInterface
{
    protected $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $interface)
    {
        $this->objectManager = $interface;
    }


    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $coll = $this->objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class);
        $coll->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4)
            ->addFieldToFilter('is_user_defined', ['eq' => 1])
            ->addFieldToFilter('frontend_input', ['neq' => 'boolean']);
        $attributeRepository = $coll->load()->getItems();
        $options = [];
        foreach ($attributeRepository as $attributes) {
            if ($attributes->usesSource()) {
                $attributeId = $attributes->getAttributeId();
                $attributeName = $attributes->getAttributeCode();
                $options[] = ['value' => $attributeId, 'label' => $attributeName];
            }
        }
        return $options;
    }
}
