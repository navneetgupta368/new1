<?php
/**
 * Sunarc Software.
 *
 * @category  Sunarc
 * @package   Sunarc_Multishipping
 * @author    Sunarc
 */
namespace Sunarc\Splitorderpro\Plugin\Shipping\Model;

use Magento\Framework\Session\SessionManager;

class ShippingInformationManagement
{
    /**
     * @var Magento\Framework\Session\SessionManager
     */
    protected $_coreSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Sunarc\Multishipping\Logger\Logger
     */

    public function __construct(
        SessionManager $coreSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_coreSession = $coreSession;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
       
            $extAttributes = $addressInformation->getExtensionAttributes();
            $selectedShipping = $extAttributes->getSelectedOrderShipping();
            $multiCustomship = $extAttributes->getMultiOrderShipping();
        if (isset($multiCustomship) && !empty($multiCustomship)) {
            $this->_coreSession->setSelectedAmount($multiCustomship);
        }

        if (isset($selectedShipping) && !empty($selectedShipping)) {
            $this->_coreSession->setSelectedMethods($this->jsonHelper->jsonDecode($selectedShipping));
        }
    }
}
