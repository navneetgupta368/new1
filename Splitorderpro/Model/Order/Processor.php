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

namespace Sunarc\Splitorderpro\Model\Order;

class Processor
{

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->priceCurrency = $priceCurrency;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
    }
    
    /**
     * Separate orders on warehouses
     *
     * @param $result
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */

   

    public function separateOrders($result, $order)
    {
       /* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test22222.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($result);*/
        $list = [];


        foreach ($result as $key => $itemEntity) {
            if (!isset($list[$itemEntity['split_attribute']])) {
                $list[$itemEntity['split_attribute']] = [];
            }
            $list[$itemEntity['split_attribute']][] = $key;
        }


        if (count($list) <= 1) {
            return [$result, [$order]];
        }


        $orders = [];
        $numberOrder = 1;
        $baseShippingAmount = $order->getBaseShippingAmount();

        if ($baseShippingAmount) {
            $baseShippingAmount = round($order->getBaseShippingAmount() / count($list), 4);
                    

        }
        $shippingBaseTax = $order->getBaseShippingTaxAmount();
        if($shippingBaseTax){
         $shippingBaseTax = round($order->getBaseShippingTaxAmount() / count($list), 4);
        }

        $totalOrder=1;
        foreach ($list as $k => $items) {
            if ($numberOrder > 1) {
                $newOrder = $this->orderFactory->create();
                $newOrder->setData($this->beforeDataOrder($order->getData()));
                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment = $order->getPayment();
                $payment->setId(null);
                $payment->setParentId(null);
                $newOrder->setPayment($payment);

                $addresses = $order->getAddresses();
                foreach ($addresses as $address) {
                    $address->setId(null);
                    $address->setParentId(null);
                    $newOrder->addAddress($address);
                }

                /** Save state and status value for next save to leave order pending */
                $state = $newOrder->getState();
                $status = $newOrder->getStatus();
                $this->orderRepository->save($newOrder);

                foreach ($items as $item) {
                    $orderItem = $this->orderItemRepository->get($result[$item]['order_item_id']);
                    
if($orderItem->getProductType() == 'bundle'){
      
            foreach ($order->getAllItems() as $_item) {  
                if($_item->getParentItemId())
                {
            if($orderItem->getItemId() == $_item->getParentItemId()){
                 $parentOrderItembundle = $this->orderItemRepository->get($_item->getItemId());
                 $parentOrderItembundle->setOrderId($newOrder->getId());
                 $this->orderItemRepository->save($parentOrderItembundle);
                }
            }
        }
    }


                    if ($orderItem->getParentItemId()) {
                        $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                        $parentOrderItem->setOrderId($newOrder->getId());
                        $this->orderItemRepository->save($parentOrderItem);
                    }
                    $orderItem->setOrderId($newOrder->getId());
                    $this->orderItemRepository->save($orderItem);
                    $result[$item]['order_id'] = $newOrder->getId();
                }
                /** Change state from complete */
                if ($newOrder->getState() != $state || $newOrder->getStatus() != $status) {
                    $newOrder->setState($state);
                    $newOrder->setStatus($status);
                    $this->orderRepository->save($newOrder);
                }
             $shippingDescription =  $order->getShippingDescription();

  $shippingData=['description'=>$shippingDescription,'amount'=>$baseShippingAmount,'tax' =>$shippingBaseTax];

                $order = $this->changeOrderData(
                    $result,
                    $items,
                    $newOrder,
                    $shippingData
                );
                $orders[] = $order;
            } else {

               
$shippingDescription =  $order->getShippingDescription();

  $shippingData=['description'=>$shippingDescription,'amount'=>$baseShippingAmount,'tax' =>$shippingBaseTax];
                $order = $this->changeOrderData(
                    $result,
                    $items,
                    $order,
                    $shippingData
                );
                $orders[] = $order;
            }
            $totalOrder++;
            $numberOrder++;
        }

        return [$result, $orders];
    }
    

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param int $warehouse
     * @param float $amount
     * @return float
     */
    private function setShippingAmount($order, $warehouse, $amount, $totalOrder,$shippingAll)
    {
       

        $shippingAmount = '';
        $shippingDescription = '';
        if (!empty($shippingAll)) {
            $counter = 0;
            foreach ($shippingAll as $shipdata) {
                if ($shipdata['orderid'] == $totalOrder) {
                    $shippingDescription = $shipdata['shipping_description'];
                    $shippingAmount = $shipdata['price'];;

                    // $selectedShippingMethod = $shipdata['carrier_code'];
                }
                $counter++;
            }

        }

        $shippingData=['description'=>$shippingDescription,'amount'=>$shippingAmount];
        return $shippingData;
    }

    /**
     * each create order after separate
     *
     * @param array $result
     * @param $items
     * @param \Magento\Sales\Model\Order $order
     * @param float $baseShippingAmount
     * @return \Magento\Sales\Model\Order
     */
    private function changeOrderData($result, $items, $order, $baseShipping)
    {
        $totalQty = 0;
        $subTotal = 0;
        $baseSubTotal = 0;
        $subTotalInclTax = 0;
        $baseSubTotalInclTax = 0;
        $discount = 0;
        $baseDiscount = 0;
        $tax = 0;
        $baseTax = 0;

        foreach ($items as $item) {
            $orderItem = $this->orderItemRepository->get($result[$item]['order_item_id']);
            if ($orderItem->getParentItemId()) {
                $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                $totalQty += $parentOrderItem->getQtyOrdered();
                $subTotal += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getPrice()
                );
                $baseSubTotal += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getBasePrice()
                );
                $subTotalInclTax += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getPriceInclTax()
                );
                $baseSubTotalInclTax += $this->priceCurrency->round(
                    $parentOrderItem->getQtyOrdered() * $parentOrderItem->getBasePriceInclTax()
                );
                if ($parentOrderItem->getDiscountPercent()) {
                    $discount += $this->priceCurrency->round(
                        $subTotal * ($parentOrderItem->getDiscountPercent() / 100)
                    );
                    $baseDiscount += $this->priceCurrency->round(
                        $baseSubTotal * ($parentOrderItem->getDiscountPercent() / 100)
                    );
                }
                if ($parentOrderItem->getTaxPercent()) {
                    $tax += $this->priceCurrency->round(
                        $subTotal * ($parentOrderItem->getTaxPercent() / 100)
                    );
                    $baseTax += $this->priceCurrency->round(
                        $baseSubTotal * ($parentOrderItem->getTaxPercent() / 100)
                    );
                }
            } else {


               

                if ($orderItem->getPrice() > 0) {
                    $totalQty += $orderItem->getQtyOrdered();
                    $subTotal += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getPrice()
                    );
                    $baseSubTotal += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getBasePrice()
                    );
                    $subTotalInclTax += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getPriceInclTax()
                    );
                    $baseSubTotalInclTax += $this->priceCurrency->round(
                        $orderItem->getQtyOrdered() * $orderItem->getBasePriceInclTax()
                    );
                    if ($orderItem->getDiscountPercent()) {
                        $discount += $this->priceCurrency->round(
                            $subTotal * ($orderItem->getDiscountPercent() / 100)
                        );
                        $baseDiscount += $this->priceCurrency->round(
                            $baseSubTotal * ($orderItem->getDiscountPercent() / 100)
                        );
                    }
                    if ($orderItem->getTaxPercent()) {
                        $tax += $this->priceCurrency->round(
                            $subTotal * ($orderItem->getTaxPercent() / 100)
                        );
                        $baseTax += $this->priceCurrency->round(
                            $baseSubTotal * ($orderItem->getTaxPercent() / 100)
                        );
                    }
                }
            }
        }
        $amountDiscount = $discount;
        $baseAmountDiscount = $baseDiscount;
        if ($discount > 0) {
            $amountDiscount = -$discount;
            $baseAmountDiscount = -$baseDiscount;
        }
        $shippingAmount = $this->priceCurrency->convert($this->priceCurrency->round($baseShipping['amount']));

        $order->setBaseDiscountAmount($baseAmountDiscount);
        $order->setDiscountAmount($amountDiscount);
        $order->setBaseTaxAmount($baseTax+$baseShipping['tax'] );
        $order->setTaxAmount($tax+$baseShipping['tax']);
        $order->setBaseGrandTotal($baseSubTotal - $baseDiscount + $baseTax + $baseShipping['tax'] + $baseShipping['amount']);
        $order->setGrandTotal($subTotal - $discount + $tax + $baseShipping['tax'] + $shippingAmount);
        $order->setBaseSubtotal($baseSubTotal);
        $order->setSubtotal($subTotal);
        $order->setTotalQtyOrdered($totalQty);
        $order->setBaseSubtotalInclTax($baseSubTotalInclTax);
        $order->setSubtotalInclTax($subTotalInclTax);
        $order->setBaseTotalDue($baseSubTotal - $baseDiscount);
        $order->setTotalDue($subTotal - $discount);
        $order->setBaseShippingAmount($baseShipping['amount']);       
        $order->setBaseShippingInclTax($baseShipping['amount']+$baseShipping['tax']);
        $order->setShippingAmount($shippingAmount);
        $order->setShippingDescription($baseShipping['description']);
        $order->setShippingInclTax($shippingAmount+$baseShipping['tax']);
        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function beforeData($data)
    {
        unset($data['item_id']);
        $data['quote_item_id'] = null;
        $data['parent_item_id'] = null;

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function beforeDataOrder($data)
    {
        $unsetKeys = ['entity_id', 'parent_id', 'item_id', 'order_id'];
        foreach ($unsetKeys as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }

        $unsetKeys = ['increment_id', 'items', 'addresses', 'payment'];
        foreach ($unsetKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
