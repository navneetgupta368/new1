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
namespace Sunarc\Splitorderpro\Api\Data;

/**
 * @api
 */
interface SplitattrInterface
{
    /**
     * ID
     *
     * @var string
     */
    const SPLITATTR_ID = 'splitattr_id';

    /**
     * Select Attribute For Split Order attribute constant
     *
     * @var string
     */
    const SPLIT_ORDER_ATTR = 'split_order_attr';

    /**
     * Priority attribute constant
     *
     * @var string
     */
    const PRIORITY = 'priority';

    /**
     * Attribute Options attribute constant
     *
     * @var string
     */
    const ATTR_VALUE = 'attr_value';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getSplitattrId();

    /**
     * Set ID
     *
     * @param int $splitattrId
     * @return SplitattrInterface
     */
    public function setSplitattrId($splitattrId);

    /**
     * Get Select Attribute For Split Order
     *
     * @return mixed
     */
    public function getSplitOrderAttr();

    /**
     * Set Select Attribute For Split Order
     *
     * @param mixed $splitOrderAttr
     * @return SplitattrInterface
     */
    public function setSplitOrderAttr($splitOrderAttr);

    /**
     * Get Priority
     *
     * @return mixed
     */
    public function getPriority();

    /**
     * Set Priority
     *
     * @param mixed $priority
     * @return SplitattrInterface
     */
    public function setPriority($priority);

    /**
     * Get Attribute Options
     *
     * @return mixed
     */
    public function getAttrValue();

    /**
     * Set Attribute Options
     *
     * @param mixed $attrValue
     * @return SplitattrInterface
     */
    public function setAttrValue($attrValue);
}
