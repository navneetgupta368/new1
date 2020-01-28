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
namespace Sunarc\Splitorderpro\Controller\Adminhtml\Splitattr;

class MassDelete extends \Sunarc\Splitorderpro\Controller\Adminhtml\Splitattr\MassAction
{
    /**
     * @param \Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr
     * @return $this
     */
    protected function massAction(\Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr)
    {
        $this->splitattrRepository->delete($splitattr);
        return $this;
    }
}
