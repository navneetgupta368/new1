<?php
/**
 * Sunarc_Splitorderpro
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Sunarc_Splitorderpro
 *
 * @category Sunarc_Splitorderpro
 * @package Sunarc_Splitorderpro
 * @copyright Copyright (c) 2014 Zowta LLC (http://www.sunarctechnologies.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author Sunarc_Splitorderpro Team support@sunarctechnologies.com
 *
 */

namespace Sunarc\Splitorderpro\Plugin\Quote\Observer;

use Magento\Framework\Registry;

class SubmitObserver
{
    /**
     * @var \Sunarc\Splitorderpro\Helper\Data
     */
    private $helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * SubmitObserver constructor.
     * @param \Sunarc\Splitorderpro\Helper\Data $helper
     */
    public function __construct(
        \Sunarc\Splitorderpro\Helper\Data $helper,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * avoid send email for Core. Email will be sent by Multiinventory.
     * Becouse order can be splitted
     *
     * @param \Magento\Quote\Observer\SubmitObserver $submitObserver
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function beforeExecute(
        \Magento\Quote\Observer\SubmitObserver $submitObserver,
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();


        if ($this->helper->getConfigModuleEnabled()) {
            $this->registry->unregister('splitorder_cant_send_new_email');
            if (!$order->getCanSendNewEmailFlag()) {
                $this->registry->register('splitorder_cant_send_new_email', true);
            }

            // avoid send email for Core. Email will be sent by splitorder. Becouse order can be splitted
            $order->setCanSendNewEmailFlag(false);
        }
    }
}
