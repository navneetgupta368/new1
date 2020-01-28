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


namespace Sunarc\Splitorderpro\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\ScopeInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;

class EmailNotification
{

    const XML_PATH_EMAIL_NEW_ORDER = 'sales_email/order/template';
    const SENDER_EMAIL='sales_email/order/identity';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;


    /**
     * EmailNotification constructor.
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        PaymentHelper $paymentHelper,
        Renderer $renderer
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->renderer=$renderer;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param $template
     * @param $sender
     * @param array $templateParams
     * @param null $storeId
     * @param null $email
     */
    private function sendEmailTemplate(
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {

        $templateId = $this->scopeConfig->getValue($template, 'store', $storeId);
        if ($email) {

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
//$logger->info(print_r($yourArray, true));
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($sender);

            if (strpos($email, ",") === false) {
                $transport->addTo($email);
            } else {
                $emails = explode(",", $email);
                $counter = 1;
                foreach ($emails as $record) {
                    if ($counter == 1) {
                        $transport->addTo($record);
                    } else {
                        $transport->addCc($record);
                    }
                    $counter++;
                }
            }
            $mailTransport = $transport->getTransport();

            $mailTransport->sendMessage();
        }
    }


    /**
     * @param $order
     */
    public function setNewOrder($order)
    {
        $email = $order->getCustomerEmail();
    
        $this->sendEmailTemplate(
            self::XML_PATH_EMAIL_NEW_ORDER,
            $this->senderEmail($this->storeManager->getStore()->getId()),
            [
                'store' => $this->storeManager->getStore(),
                'order' => $order,
                'payment_html' =>  $this->getPaymentHtml($order, $this->storeManager->getStore()->getId()),
                'formattedBillingAddress' => $this->renderer->format($order->getBillingAddress(), 'html'),
               'formattedShippingAddress' => $this->renderer->format($order->getShippingAddress(), 'html'),
                'url' => $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
            ],
            $this->storeManager->getStore()->getId(),
            $email
        );
    }

    public function senderEmail($storeId = null)
    {
        $sender=[];
        $sender ['email'] = $this->scopeConfig->getValue(
            self::SENDER_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $sender['name'] = 'Sales';

        return $sender;
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $order, $storeId)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $storeId
        );
    }
}
