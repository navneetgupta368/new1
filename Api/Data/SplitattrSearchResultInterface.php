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
interface SplitattrSearchResultInterface
{
    /**
     * Get Splitattrs list.
     *
     * @return \Sunarc\Splitorderpro\Api\Data\SplitattrInterface[]
     */
    public function getItems();

    /**
     * Set Splitattrs list.
     *
     * @param \Sunarc\Splitorderpro\Api\Data\SplitattrInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
