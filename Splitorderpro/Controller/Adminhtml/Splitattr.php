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
namespace Sunarc\Splitorderpro\Controller\Adminhtml;

abstract class Splitattr extends \Magento\Backend\App\Action
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
     * constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface $splitattrRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface $splitattrRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->coreRegistry        = $coreRegistry;
        $this->splitattrRepository = $splitattrRepository;
        $this->resultPageFactory   = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * filter values
     *
     * @param array $data
     * @return array
     */
    protected function filterData($data)
    {
        if (isset($data['attr_value'])) {
            if (is_array($data['attr_value'])) {
                $data['attr_value'] = implode(',', $data['attr_value']);
            }
        }
        return $data;
    }
}
