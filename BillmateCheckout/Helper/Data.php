<?php

namespace Billmate\BillmateCheckout\Helper;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Billmate\BillmateCheckout\Model\Payment\BillmateCheckout;
use Magento\Quote\Model\ShippingMethodManagement;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MODULE_NAME = 'Billmate_BillmateCheckout';
    const BM_PENDING_STATUS = 'pending';
    const BM_DENY_STATUS = 'canceled';
    const BM_APPROVE_STATUS = 'processing';

    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;

    /**
     * @var float
     */
	protected $shippingPrice;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var array
     */
    protected $defaultAddress = [
        'firstname' => 'Testperson',
        'lastname' => 'Approved',
        'street' => 'Teststreet',
        'city' => 'Testcity',
        'country_id' => 'SE',
        'postcode' => '12345',
        'telephone' => '0700123456'
    ];

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Quote\Model\Quote\Address\Rate    $shippingRate
     * @param \Magento\Sales\Api\Data\OrderInterface     $order
     * @param \Magento\Checkout\Model\Session            $_checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory          $quote
     * @param \Magento\Framework\View\LayoutFactory      $layoutFactory
     * @param ProductMetadataInterface                   $metaData
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param ModuleListInterface                        $moduleList
     */
    public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Quote\Model\Quote\Address\Rate $shippingRate,
		\Magento\Sales\Api\Data\OrderInterface $order,
		\Magento\Checkout\Model\Session $_checkoutSession,
		\Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ProductMetadataInterface $metaData,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        ShippingMethodManagement $shippingManager,
        ModuleListInterface $moduleList,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter
	){
        $this->orderInterface = $order;
        $this->shippingRate = $shippingRate;
        $this->checkoutSession = $_checkoutSession;
        $this->quote = $quote;
        $this->logger = $context->getLogger();
        $this->metaData = $metaData;
        $this->layoutFactory = $layoutFactory;
        $this->totalsCollector = $totalsCollector;
        $this->shippingManager = $shippingManager;
        $this->_moduleList = $moduleList;
        $this->converter = $converter;

        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function prepareCheckout()
    {
        if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
            $this->getQuote()->getBillingAddress()->addData($this->getAddress());
            $shippingAddress = $this->getQuote()->getShippingAddress()->addData($this->getAddress());
            $this->getQuote()->setShippingAddress($shippingAddress);
            $shippingAddress->save();
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCartContent()
    {
        $layout = $this->layoutFactory->create();
        return $layout->createBlock('Billmate\BillmateCheckout\Block\Cart\Content')
            ->setTemplate('cart/content.phtml')->toHtml();
    }

    /**
     * @param $row
     *
     * @return mixed
     */
    public function correctSymbols($row)
    {
        $replaceFrom = ['Ã…','Ã„','Ã–','Ã¥','Ã¤','Ã¶'];
        $replaceTo = ['Å','Ä','Ö','å','ä','ö'];
        return str_replace($replaceFrom,$replaceTo,$row);
    }

    /**
     * @param $input
     */
	public function setShippingAddress($shippingData)
    {
        $shippingData = $this->prepareShippingData($shippingData);
        $quote = $this->getQuote();
        $billmateTelephone = $this->getSessionData('billmate_telephone');
        $shippingAddress = [
            'firstname' => $shippingData['firstname'],
            'lastname' => $shippingData['lastname'],
            'street' => $shippingData['street'],
            'city' => $shippingData['city'],
            'country_id' => $shippingData['country_id'],
            'postcode' => $shippingData['postcode'],
        ];
        if (isset($shippingData['telephone']) || $billmateTelephone) {
            $shippingAddress['telephone'] = $billmateTelephone?$billmateTelephone:$shippingData['telephone'];
        }

        $quote->getShippingAddress()->addData($shippingAddress);
        $this->setSessionData('billmate_shipping_address',$shippingAddress);
	}

    /**
     * @param $shippingData
     */
	public function setBillingAddress($shippingData)
    {
        $shippingData = $this->prepareShippingData($shippingData);
        $billingAddress = [
            'firstname' => $shippingData['firstname'],
            'lastname' => $shippingData['lastname'],
            'street' => $shippingData['street'],
            'city' => $shippingData['city'],
            'country_id' => $shippingData['country_id'],
            'postcode' => $shippingData['postcode'],
            'telephone' => $shippingData['telephone'],
            'email' => $shippingData['email'],
        ];

        $this->getQuote()->getBillingAddress()->addData($billingAddress);
        $this->setSessionData('billmate_billing_address', $billingAddress);
        $this->setSessionData('billmate_telephone', $shippingData['telephone']);
        if ($shippingData['email']) {
            $this->setSessionData('billmate_email', $shippingData['email']);
        }

		if ($this->getSessionData('billmate_shipping_address')) {
			$this->setShippingAddress($shippingData);
		}
	}

    /**
     * @param $shippingData
     *
     * @return array
     */
	protected function prepareShippingData($shippingData)
    {
        $shippingData['firstname'] = $this->correctSymbols($shippingData['firstname']);
        $shippingData['lastname'] = $this->correctSymbols($shippingData['lastname']);
        $shippingData['street'] = $this->correctSymbols($shippingData['street']);
        $shippingData['city'] = $this->correctSymbols($shippingData['city']);

        return $shippingData;
    }

    /**
     * @param $methodInput
     */
    public function setShippingMethod($methodInput)
    {
        $this->prepareCheckout();
		$shippingAddress = $this->getQuote()->getShippingAddress();
        $shippingAddress->setShippingMethod($methodInput);
		$shippingAddress->collectShippingRates();
        $shippingAddress->save();
		$this->getQuote()->collectTotals();
        $this->getQuote()->save();
        $this->setSessionData('shipping_code', $methodInput);
    }

    /**
     * @param $code
     */
    public function setDiscountCode($code)
    {
	    $this->getQuote()
            ->setCouponCode($code)
            ->collectTotals()
            ->save();
        $this->setSessionData('billmate_applied_discount_code', $code);
    }

	public function clearSession()
    {
		$this->checkoutSession->clearStorage();
		$this->checkoutSession->clearQuote();
		$this->clearBmSession();
		session_unset();
	}

	public function clearBmSession()
    {
        $this->setSessionData('iframe_url', null);
        $this->setSessionData('shippingPrice', null);
        $this->setSessionData('shipping_code', null);
        $this->setSessionData('billmate_shipping_tax', null);
        $this->setSessionData('billmate_shipping_address', null);
        $this->setSessionData('billmate_billing_address', null);
        $this->setSessionData('billmate_email', null);
        $this->setSessionData('billmate_applied_discount_code', null);
        $this->setSessionData('billmate_checkout_id', null);
        $this->setSessionData('billmate_payment_method', null);
        $this->setSessionData('bm-inc-id',null);
    }

    /**
     * @param $methodCode int
     */
	public function setBmPaymentMethod($methodCode)
    {
        $method = 'billmate_checkout';
        $this->setSessionData('billmate_payment_method', $method);
	}

    public function getPaymentMethod()
    {
        return BillmateCheckout::PAYMENT_CODE_CHECKOUT;
    }

    /**
     * @return string
     */
    public function getClientVersion()
    {
        return "Magento:".$this->getMagentoVersion()." PLUGIN:" . $this->getPluginVersion();
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        $version = $this->metaData->getVersion();
        return $version;
    }

    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }

    /**
     * @param $price
     *
     * @return mixed
     */
    public function priceToCents($price)
    {
        return $price * 100;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getItems()
    {
       return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }

        return $this->_quote;
    }

    /**
     * @return array
     */
    public function getShippingMethodsRates()
    {
        $this->prepareCheckout();
        $methods = [];
        $quote = $this->getQuote();
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $convertedRate = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
                $rate->setPriceInclTax($convertedRate->getPriceInclTax());
                $rate->setPriceExclTax($convertedRate->getPriceExclTax());
                $methods[] = $rate;
            }
        }

        if (!$shippingAddress->getShippingMethod() && $methods) {
            $rate = current($methods);
            $this->setShippingMethod($rate->getCode());
        }

        return $methods;

    }

    /**
     * @param $key
     * @param $value
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function setSessionData($key, $value)
    {
        return $this->_getCheckoutSession()->setData($key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function getSessionData($key)
    {
        return $this->_getCheckoutSession()->getData($key);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @param $methodCode
     *
     * @return bool
     */
    public function isActiveShippingMethod($methodCode)
    {
        $activeMethod = $this->getQuote()
            ->getShippingAddress()
            ->getShippingMethod();
        return  $activeMethod == $methodCode;
    }

    /**
     * @return array
     */
    protected function getAddress()
    {
        return $this->defaultAddress;
    }

    /**
     * @param $data
     */
    public function addLog($data)
    {
        if (!is_array($data)) {
            $data = ['data' => $data];
        }

        $logData = [
            'date' => date('Y-m-d H:i:s'),
        ];
        $logData = $logData + $data;
        $this->logger->error(print_r($logData, true));
    }

    /**
     * @param $type
     *
     * @return \Magento\Quote\Model\Quote\Address\Total
     */
    public function getTotalRow($type)
    {
        $this->getQuote()->getShippingAddress()->getData();
        $totals = $this->getQuote()->getTotals();
        if(isset($totals[$type])) {
            return $totals[$type];
        }
        return current($totals);
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderByIncrementId($orderIncId)
    {
        return $this->orderInterface->loadByIncrementId($orderIncId);
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderById($orderId)
    {
        return $this->orderInterface->load($orderId);
    }


    /**
     * @return mixed
     */
    public function getPendingStatus()
    {
        return self::BM_PENDING_STATUS;
    }

    /**
     * @return mixed
     */
    public function getDenyStatus()
    {
        return self::BM_DENY_STATUS;
    }

    /**
     * @return mixed
     */
    public function getApproveStatus()
    {
        return self::BM_APPROVE_STATUS;
    }
}
