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

class InlineEdit extends \Sunarc\Splitorderpro\Controller\Adminhtml\Splitattr
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Splitattr repository
     *
     * @var \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface
     */
    protected $splitattrRepository;

    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Data object processor
     *
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * Splitattr resource model
     *
     * @var \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr
     */
    protected $splitattrResourceModel;

    /**
     * constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface $splitattrRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr $splitattrResourceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface $splitattrRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr $splitattrResourceModel
    ) {
        $this->dataObjectProcessor    = $dataObjectProcessor;
        $this->dataObjectHelper       = $dataObjectHelper;
        $this->jsonFactory            = $jsonFactory;
        $this->splitattrResourceModel = $splitattrResourceModel;
        parent::__construct($context, $coreRegistry, $splitattrRepository, $resultPageFactory);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $splitattrId) {
            /** @var \Sunarc\Splitorderpro\Model\Splitattr|\Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr */
            $splitattr = $this->splitattrRepository->getById((int)$splitattrId);
            try {
                $splitattrData = $postItems[$splitattrId];
                $splitattrData = $this->filterData($splitattrData);
                $this->dataObjectHelper->populateWithArray($splitattr, $splitattrData, \Sunarc\Splitorderpro\Api\Data\SplitattrInterface::class);
                $this->splitattrResourceModel->saveAttribute($splitattr, array_keys($splitattrData));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithSplitattrId($splitattr, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithSplitattrId($splitattr, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithSplitattrId(
                    $splitattr,
                    __('Something went wrong while saving the split order attribute.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Splitattr id to error message
     *
     * @param \Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithSplitattrId(\Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr, $errorText)
    {
        return '[Splitattr ID: ' . $splitattr->getId() . '] ' . $errorText;
    }
}
