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

namespace Sunarc\Splitorderpro\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;

class CheckoutAllSubmitAfterObserver implements ObserverInterface
{

    /**
     * @var \Sunarc\Splitorderpro\Helper\Data
     */
    private $helper;

    /**
     * @var \Sunarc\Splitorderpro\Model\Order\Processor
     */
    private $processor;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    private $orderProcessor;

    /**
     * CheckoutAllSubmitAfterObserver constructor.
     * @param \Sunarc\Splitorderpro\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Sunarc\Splitorderpro\Model\Order\Processor $processor
     */
    public function __construct(
        \Sunarc\Splitorderpro\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        GetSourceItemsBySkuInterface $sourceItemsBySku,
        \Sunarc\Splitorderpro\Model\Order\Processor $orderProcessor
    ) {
        $this->helper           = $helper;
        $this->messageManager   = $messageManager;
        $this->orderSender      = $orderSender;
        $this->checkoutSession  = $checkoutSession;
        $this->registry         = $registry;
        $this->getSourceItemsBySku=$sourceItemsBySku;
        $this->orderProcessor   = $orderProcessor;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {

        $isEnabled = $this->helper->getConfigModuleEnabled();
        $orders=[];
        if (!($isEnabled)) {
            return $this;
        }
        $selectCondition = $this->helper->getConditionEnabled();
        $result = [];

        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$order) {
            return $this;
        }
        $quote->setInventoryProcessed(true);
        $orders = [$order];


         try {

            if($selectCondition == 1)
            {
               

                $result= $this->splitDefaultByItem($order);
            }
            elseif ($selectCondition == 2)
            {
               

                $result= $this->splitIfAttrExist($order);
            }
            elseif ($selectCondition == 3)
            {
               

                $result= $this->splitOrderOnAttrBasis($order);
            }
            elseif ($selectCondition == 4)
            {
               

                $result= $this->splitOrderOnWarehouseBasis($order);
            }
            else{
               

                $result= $this->splitDefaultByItem($order);
            }
         

            list($result, $orders) = $this->orderProcessor->separateOrders($result, $order);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the order for now.'));
        }



       

        $orderIds = [];
        /** @var \Magento\Sales\Model\Order| $order */
        foreach ($orders as $order) {
          

            $orderIds[$order->getId()] = $order->getIncrementId();
            $order->setItems(null);
          //  if ($this->isCanSendEmail($quote)) {
                $this->orderSender->send($order);
          // }
           // $this->helper->setOrderEmail($order);
        }

        if (count($orderIds) > 1) {

            $this->checkoutSession->setSeparetedOrderIds($orderIds);
        }

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item | \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    public function isSimple($item)
    {
        return $item->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item | \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    public function isVirtual($item)
    {
        return $item->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;
    }
    /**
     * @param \Magento\Sales\Model\Order\Item | \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    public function isDownloadable($item)
    {
        return $item->getProduct()->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    }

   /**
     * @param \Magento\Sales\Model\Order\Item | \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    public function isBundle($item)
    {
        return $item->getProduct()->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }


    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param int $splitAttribute
     * @return array
     */
    public function getArrayItem($item, $splitAttribute)
    {

      
        return [
            'order_id' => $item->getOrderId(),
            'order_item_id' => $item->getId(),
            'split_attribute' => $splitAttribute,
            'product_id' => $item->getProductId(),
            'qty' => $item->getQtyOrdered()
        ];
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    private function isCanSendEmail($quote)
    {

        return !(bool)$quote->getPayment()->getOrderPlaceRedirectUrl()
            && !$this->registry->registry('splitorder_cant_send_new_email')
            && $quote->getCustomerNoteNotify() === true;
    }

    public function splitDefaultByItem($order){
        $result = [];
        $i=0;
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getProductType()=='configurable'){
                $childItems = $item->getChildrenItems();
                    foreach($childItems as $childitem)
                    {
                        // if($childitem->getSplitAttributeValue() != '')
                        // $result[] = $this->getArrayItem($childitem, $childitem->getSplitAttributeValue());
                        // else
                        $result[] = $this->getArrayItem($childitem, 'remaining'.$i);
                        $i++;
                    }
            }
            if ($item->getParentItemId() || $this->isSimple($item) ||$this->isDownloadable($item) || $this->isVirtual($item)||$this->isBundle($item)) {

                    
                    // if($item->getSplitAttributeValue() != '')
                    //     $result[] = $this->getArrayItem($item, $item->getSplitAttributeValue());
                    // else
                        $result[] = $this->getArrayItem($item, 'remaining'.$i);
                        $i++;

                }
            }




            // if ($item->getParentItemId() || $this->isSimple($item) ||$this->isDownloadable($item) || $this->isVirtual($item)) {
               
            //         $result[] = $this->getArrayItem($item, 'remaining'.$i);
            //         $i++;
               
            // }
        
        return $result;
    }

     public function splitIfAttrExist($order){
         $checkAttribute=$this->splitAttributeExist($order);
        if (!($checkAttribute)) {
            $result = [];
        $i=0;
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getProductType()=='configurable'){
                $childItems = $item->getChildrenItems();
                    foreach($childItems as $childitem)                    {
                       
                        $result[] = $this->getArrayItem($childitem, 'remaining');
                        $i++;
                    }
            }
            if ($item->getParentItemId() || $this->isSimple($item) ||$this->isDownloadable($item) || $this->isVirtual($item)||$this->isBundle($item)) {
                $result[] = $this->getArrayItem($item, 'remaining');
                        $i++;
                }
            }
              //$result[] = $this->getArrayItem($item, 'remaining');
        } else {
            return $this->splitDefaultByItem($order);
        }
        return $result;
    }

   public function splitOrderOnWarehouseBasis($order){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $tmparr = [];   
        $i=0; 
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getProductType()=='configurable'){
                $childItems = $item->getChildrenItems();
                    foreach($childItems as $childitem)
                    {                       
                        $result[] = $this->getArrayItem($childitem, 'remaining');
                        $i++;
                    }
            }
            $id=$item->getProductId(); 
            if($this->isSimple($item) || $this->isVirtual($item)){
                $product= $objectManager->get('\Magento\Catalog\Model\Product')->load($id);
                $sku= $item->getSku();                          
                $product_type=$item->getProduct()->getTypeId();
                $order_quantity=$item->getQtyOrdered();
                $order_id = $item->getOrderId();
                $order_item_id = $item->getId();                 
              
                $sourceItemsBySku = $this->getSourceItemsBySku->execute($product->getSku());
                $config['stockData'][$id] = [];
                
                foreach ($sourceItemsBySku as $sourceItem) {
                    $source = $sourceItem->getSourceCode(); //get the source name of the product
                    $quantity = $sourceItem->getQuantity();                
                    $config['stockData'][$id][$source] = $quantity;
                   
                 }
                 
            }    
            $result[] = $this->getArrayItem($item, $source);         
           // $result[] = $this->getArrayItem($item, '); 
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test22222.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($source);
        }
        
       return $result;
    }


     public function splitOrderOnAttrBasis($order){
        
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getProductType()=='configurable'){
                $childItems = $item->getChildrenItems();
                    foreach($childItems as $childitem)
                    {
                        if($childitem->getSplitAttributeValue() != '')
                        $result[] = $this->getArrayItem($childitem, $childitem->getSplitAttributeValue());
                        else
                        $result[] = $this->getArrayItem($childitem, 'remaining');
                    }
            }
            if ($item->getParentItemId() || $this->isSimple($item) ||$this->isDownloadable($item) || $this->isVirtual($item)||$this->isBundle($item)) {

                    
                    if($item->getSplitAttributeValue() != '')
                        $result[] = $this->getArrayItem($item, $item->getSplitAttributeValue());
                    else
                        $result[] = $this->getArrayItem($item, 'remaining');

                }
            }
             return $result;            
       
        }
         public function splitAttributeExist($order){
        
        $checkattr = 0;
            foreach ($order->getAllVisibleItems() as $item) {
            if($item->getProductType()=='configurable'){
                $childItems = $item->getChildrenItems();
                    foreach($childItems as $childitem)
                    {
                        if($childitem->getSplitAttributeValue() != '')
                        {
                         $checkattr = 1;
                        }
                    }
            }
            if ($item->getParentItemId() || $this->isSimple($item) ||$this->isDownloadable($item) || $this->isVirtual($item)||$this->isBundle($item)) {

                    
                    if($item->getSplitAttributeValue() != ''){
                    $checkattr = 1;
                    }
                }
            }
             return $checkattr;            
       
        }
    
}
