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

class Index extends \Sunarc\Splitorderpro\Controller\Adminhtml\Splitattr
{
    /**
     * Splitattrs list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Sunarc_Splitorderpro::splitattr');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Attribute'));
        $resultPage->addBreadcrumb(__('Splitorderpro'), __('Splitorderpro'));
        $resultPage->addBreadcrumb(__('Splitattrs'), __('Manage Attribute'));
        return $resultPage;
    }
}
