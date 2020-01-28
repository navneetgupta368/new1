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

class Delete extends \Sunarc\Splitorderpro\Controller\Adminhtml\Splitattr
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('splitattr_id');
        if ($id) {
            try {
                $this->splitattrRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The split order attribute has been deleted.'));
                $resultRedirect->setPath('sunarc_splitorderpro/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The split order attribute no longer exists.'));
                return $resultRedirect->setPath('sunarc_splitorderpro/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('sunarc_splitorderpro/splitattr/edit', ['splitattr_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem deleting the split order attribute'));
                return $resultRedirect->setPath('sunarc_splitorderpro/splitattr/edit', ['splitattr_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a split order attribute to delete.'));
        $resultRedirect->setPath('sunarc_splitorderpro/*/');
        return $resultRedirect;
    }
}
