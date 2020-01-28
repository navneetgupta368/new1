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
namespace Sunarc\Splitorderpro\Block\Adminhtml\Splitattr\Edit\Buttons;

class Generic
{
    /**
     * Widget Context
     *
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * Splitattr Repository
     *
     * @var \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface
     */
    protected $splitattrRepository;

    /**
     * constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface $splitattrRepository
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface $splitattrRepository
    ) {
        $this->context             = $context;
        $this->splitattrRepository = $splitattrRepository;
    }

    /**
     * Return Splitattr ID
     *
     * @return int|null
     */
    public function getSplitattrId()
    {
        try {
            return $this->splitattrRepository->getById(
                $this->context->getRequest()->getParam('splitattr_id')
            )->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
