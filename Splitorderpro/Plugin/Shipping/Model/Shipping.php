<?php
/**
 * @author Sunarc Team
 */

namespace Sunarc\Splitorderpro\Plugin\Shipping\Model;

use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;

class Shipping
{

    /**
     * @var SplitorderFactory
     */
    private $factory;

    /**
     * @var SplitorderRepositoryInterface
     */
    private $repostiory;

    /**
     * @var \Sunarc\Splitorderpro\Helper\Cart
     */
    private $helperCart;

    /**
     * @var \Sunarc\Splitorderpro\Model\ShippingFactory
     */
    private $whShipping;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $manager;

    protected $_checkoutSession;

    /**
     * Shipping constructor.
     * @param SplitorderFactory $factory
     * @param \Magento\Framework\Registry $registry
     * @param \Sunarc\Splitorderpro\Model\ShippingFactory $whShipping
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Sunarc\Splitorderpro\Helper\Data $helperData,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Sunarc\Splitorderpro\Model\ShippingFactory $orderdynamicShipping
    ) {
        $this->_coreSession = $coreSession;
        $this->_checkoutSession = $_checkoutSession;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->orderdynamicShipping = $orderdynamicShipping;
    }

    /**
     * Separate rates, if some wearehouses
     *
     * @param \Magento\Shipping\Model\Shipping $shipping
     * @param \Closure $work
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Shipping\Model\Shipping
     */
    public function aroundCollectRates(
        \Magento\Shipping\Model\Shipping $shipping,
        \Closure $work,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $storeId = $request->getStoreId();

        $advdata =  $this->helperData->getConfigModuleEnabled();

        $oldQuoteItems = [];
        $result = $forShipResult = [];
        $tempDataArray = [];
        $tempArray = $this->helperData->getConfig();
        $tempDataArray = $tempArray['test_config'];

        $items = $this->_checkoutSession->getQuote()->getAllVisibleItems();

        $countOrdersToBeMade = 0;
        //Added by Neha
        $shipment = '';
        $result = [];
        foreach ($tempDataArray as $key => $itemData) {
            $methods = [];

            $groupItems = $addedParents = [];
            $shipment = '';
            $productId = '';
            $countOrdersToBeMade++;  //Added by Neha
      
            foreach ($itemData as $itemq) {
                foreach ($items as $item) {

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    if($item->getProductType()=='bundle')
                        $productData = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());
                    else
                        $productData = $objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku', $item->getSku());

                /*   $productData = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getId());*/
                    if (isset($itemq['product_id'])) {
                        $productId = $itemq['product_id'];
                    }
                       
                    if ($productData->getEntityId() == $itemq['product_id']) {

                        $quoteItem = $this->_checkoutSession->getQuote()->getItemById($item->getId());
                        $parentId = $quoteItem->getParentItemId();
                        if ($parentId) {
                            $parentItem = $this->cart->getQuote()->getItemById($quoteItem->getParentItemId());
                            if (!in_array($parentId, $addedParents)) {
                                $addedParents[] = $parentId;
                                $groupItems[] = $parentItem;
                            }
                           if ($parentItem->getProductType() == 'bundle') {
                                continue;
                            }
                        }
                        $groupItems[] = $quoteItem;
                    }
                }
            }


            if ($advdata) {
                $request = $this->helperData->changeRequestItems($request, $groupItems, $this->_checkoutSession->getQuote());
            }
            $shipment = $this->shipmentCalculate($request, $work);
            $result[] = $shipment;
            foreach ($shipment->getAllRates() as $resultMethod) {
                $carrierTitle =$this->scopeConfig->getValue('carriers/'.$resultMethod->getCarrier().'/title');
                if ($key == $carrierTitle) {
                    $methods[] = [
                        'method' => $resultMethod->getMethod(),
                        'carrier_code' => $resultMethod->getCarrier() . '_' . $resultMethod->getMethod(),
                        'price' => $resultMethod->getPrice(),
                        'carrier_title' => $carrierTitle,
                        'shipping_description' => $resultMethod->getCarrierTitle() . ' - ' . $resultMethod->getMethodTitle(),
                    ];
                } elseif ($key == 'All Shipping') {
                    $methods[] = [
                        'method' => $resultMethod->getMethod(),
                        'carrier_code' => $resultMethod->getCarrier() . '_' . $resultMethod->getMethod(),
                        'price' => $resultMethod->getPrice(),
                        'carrier_title' => $carrierTitle,
                        'shipping_description' => $resultMethod->getCarrierTitle() . ' - ' . $resultMethod->getMethodTitle(),
                    ];
                }
            }
            $forShipResult[$countOrdersToBeMade] = $methods;
        }
        $this->_coreSession->unsSplitorderPrice();
        $this->_coreSession->setSplitorderPrice($forShipResult);
        $request = $this->helperData->changeRequestItems($request, $items, $this->_checkoutSession->getQuote());
        $shipment = $this->shipmentCalculate($request, $work);
        $shipping->getResult()->append($shipment);
        return $shipping;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Closure                                       $work
     *
     * @return \Magento\Shipping\Model\Rate\Result
     */
    private function shipmentCalculate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $work)
    {
        $storeId = $request->getStoreId();
        /** @var \Sunarc\Splitorderpro\Model\Shipping $orderdynamicShipping */
        $orderdynamicShipping = $this->orderdynamicShipping->create();
        $limitCarrier = $request->getLimitCarrier();

        if (!$limitCarrier) {
            foreach ($this->getCarriers($storeId) as $carrierCode => $carrierConfig) {
                $orderdynamicShipping->collectCarrierRates($carrierCode, $request);
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = [$limitCarrier];
            }
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = $this->getCarriers($storeId, $carrierCode);
                if (!$carrierConfig) {
                    continue;
                }
                $orderdynamicShipping->collectCarrierRates($carrierCode, $request);
            }
        }
        return $orderdynamicShipping->getResult();
    }

    /**
     * @param int         $storeId
     * @param string|null $carrierCode
     *
     * @return array
     */
    private function getCarriers($storeId, $carrierCode = null)
    {
        $configPath = 'carriers';
        if ($carrierCode !== null) {
            $configPath .= '/' . $carrierCode;
        }
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
