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

namespace Sunarc\Splitorderpro\Plugin\Block\Checkout\Onepage;

class Success
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    private static $ORDERS;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\Order\Config $orderConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->objectFactory   = $objectFactory;
        $this->orderFactory = $orderFactory;
        $this->httpContext = $httpContext;
        $this->orderConfig = $orderConfig;
    }

    public function beforeToHtml(\Magento\Checkout\Block\Onepage\Success $subject)
    {
        $orders = $this->getSeparateOrderIds();
        if (is_array($orders) && count($orders) > 1 && !$subject->hasData('multiple_orders')) {
            $data = [];
            foreach ($orders as $orderId) {
                $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                $data[] = $this->objectFactory->create()
                    ->addData([
                        'is_order_visible' => $this->isVisible($order),
                        'view_order_url' => $subject->getUrl(
                            'sales/order/view/',
                            ['order_id' => $order->getEntityId()]
                        ),
                        'print_url' => $subject->getUrl(
                            'sales/order/print',
                            ['order_id' => $order->getEntityId()]
                        ),
                        'can_print_order' => $this->isVisible($order),
                        'can_view_order'  => $this->canViewOrder($order),
                        'order_id'  => $order->getIncrementId()
                    ]);
            }
            $subject->setData('is_multiple_orders', true);
            $subject->setData('multiple_orders', $data);
        }
    }

    private function getSeparateOrderIds()
    {
        if (self::$ORDERS === null) {
            self::$ORDERS = $this->checkoutSession->getSeparetedOrderIds(true);
        }
        return self::$ORDERS;
    }

    /**
     * Is order visible
     *
     * @param Order $order
     * @return bool
     */
    protected function isVisible($order)
    {
        return !in_array(
            $order->getStatus(),
            $this->orderConfig->getInvisibleOnFrontStatuses()
        );
    }

    /**
     * Can view order
     *
     * @param Order $order
     * @return bool
     */
    protected function canViewOrder($order)
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)
            && $this->isVisible($order);
    }
}
