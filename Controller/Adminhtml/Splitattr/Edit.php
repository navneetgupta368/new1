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

class Edit extends \Sunarc\Splitorderpro\Controller\Adminhtml\Splitattr
{
    /**
     * Initialize current Splitattr and set it in the registry.
     *
     * @return int
     */
    protected function initSplitattr()
    {
        $splitattrId = $this->getRequest()->getParam('splitattr_id');
        $this->coreRegistry->register(\Sunarc\Splitorderpro\Controller\RegistryConstants::CURRENT_SPLITATTR_ID, $splitattrId);

        return $splitattrId;
    }

    /**
     * Edit or create Splitattr
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $splitattrId = $this->initSplitattr();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Sunarc_Splitorderpro::splitorderpro_splitattr');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Attribute'));
        $resultPage->addBreadcrumb(__('Splitorderpro'), __('Splitorderpro'));
        $resultPage->addBreadcrumb(__('Splitattrs'), __('Manage Attribute'), $this->getUrl('sunarc_splitorderpro/splitattr'));

        if ($splitattrId === null) {
            $resultPage->addBreadcrumb(__('New Split Attribute'), __('New Split Attribute'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Split Attribute'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Split Attribute'), __('Edit Split Attribute'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->splitattrRepository->getById($splitattrId)->getPriority()
            );
        }
        return $resultPage;
    }
}
