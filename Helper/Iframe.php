<?php
namespace Billmate\BillmateCheckout\Helper;

/**
 * Class Iframe
 * @package Billmate\BillmateCheckout\Helper
 */
class Iframe extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var float
     */
	protected $shippingPrice;

    /**
     * @var bool
     */
    protected $_updateProcessRun = false;

    /**
     * @var string
     */
    protected $_apiCallMethod = 'initCheckout';

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
	protected $dataHelper;

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
     * @var \Billmate\BillmateCheckout\Model\Payment\Handling\Invoice
     */
    private $bmInvoiceHandler;

    /**
     * Iframe constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                     $context
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager
     * @param \Magento\Quote\Model\Quote\Address\Rate                   $shippingRate
     * @param \Magento\Checkout\Model\Session                           $_checkoutSession
     * @param Config                                                    $configHelper
     * @param Data                                                      $dataHelper
     * @param \Billmate\BillmateCheckout\Model\Api\Billmate             $billmateProvider
     * @param \Magento\Tax\Model\CalculationFactory                     $taxCalculation
     * @param \Billmate\BillmateCheckout\Model\Payment\Handling\Invoice $bmInvoiceHandler
     */
    public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Quote\Model\Quote\Address\Rate $shippingRate,
		\Magento\Checkout\Model\Session $_checkoutSession,
        \Billmate\BillmateCheckout\Helper\Config $configHelper,
        \Billmate\BillmateCheckout\Helper\Data $dataHelper,
        \Billmate\BillmateCheckout\Model\Api\Billmate $billmateProvider,
        \Magento\Tax\Model\CalculationFactory $taxCalculation,
        \Billmate\BillmateCheckout\Model\Payment\Handling\Invoice $bmInvoiceHandler
	){
        $this->_storeManager = $storeManager;
        $this->shippingRate = $shippingRate;
        $this->checkoutSession = $_checkoutSession;
        $this->billmateProvider = $billmateProvider;
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
        $this->taxCalculation = $taxCalculation;
        $this->bmInvoiceHandler = $bmInvoiceHandler;

        parent::__construct($context);

    }

    /**
     * @return array|mixed
     */
    public function getIframeData()
    {
        $bmRequestData = $this->prepareRequestData();
        $method = $this->getApiMethod();

        $response = $this->billmateProvider->call(
            $method,
            $bmRequestData
        );

        if (isset($response['number'])) {
            $this->setSessionData('billmate_checkout_id', $response['number']);
        }

        return $response;
	}

    /**
     * @return array
     */
	protected function prepareRequestData()
    {
        $this->dataHelper->prepareCheckout();
        $this->runCheckIsUpdateCheckout();

        $quoteAddress = $this->dataHelper->getQuote()->getShippingAddress();
        $lShippingPrice = $quoteAddress->getShippingAmount();

        $this->shippingRate->setCode($quoteAddress->getShippingMethod());
        $this->shippingPrice = $lShippingPrice;

        $this->setSessionData('shippingPrice', $lShippingPrice);
        $this->setSessionData('shipping_code', $quoteAddress->getShippingMethod());
        $this->setSessionData('billmate_shipping_tax', $quoteAddress->getShippingTaxAmount());

        if (empty($this->getQuote()->getReservedOrderId())) {
            $this->getQuote()->reserveOrderId()->save();
        }

        $data = $this->getRequestData();

        $itemsData = $this->getItemsData();

        $data['Articles'] = array_merge($data['Articles'], $itemsData);

        $shippingAddressTotal = $this->getQuote()->getShippingAddress();
        $shippingTaxRate = $this->getShippingTaxRate();
        $invoiceFeeHandling = $this->getInvoiceFeeHandling();

        $subTotalWithoutTax = 0;
	foreach ($data['Articles'] as $article) {
            if ($article['artnr'] != '--freetext--' && isset($article['withouttax'])){
                $subTotalWithoutTax += $article['withouttax'];
            }
        }
        $cartShippingWithoutTax = $this->toCents($shippingAddressTotal->getShippingAmount());
        $cartHandlingWithoutTax = $this->toCents($invoiceFeeHandling['amount']);
        $data['Cart'] = [
            'Shipping' => [
                'withouttax' => $this->toCents($shippingAddressTotal->getShippingAmount()),
                'taxrate' => $shippingTaxRate,
                'withtax' => $this->toCents($shippingAddressTotal->getShippingInclTax()),
                'method' => $shippingAddressTotal->getShippingDescription(),
                'method_code' => $shippingAddressTotal->getShippingMethod()
            ],
            'Handling' => [
                'withouttax' => $this->toCents($invoiceFeeHandling['amount']),
                'taxrate'    => $invoiceFeeHandling['rate']
            ],
            'Total' => [
                'withouttax' => (intval($subTotalWithoutTax) + intval($cartShippingWithoutTax) + intval($cartHandlingWithoutTax)),
                'tax' => $this->toCents(
                    $shippingAddressTotal->getTaxAmount() + $shippingAddressTotal->getDiscountTaxCompensationAmount()
                    + $invoiceFeeHandling['tax_amount']
                ),
                'rounding' => $this->toCents(0),
                'withtax' => $this->toCents(
                    $shippingAddressTotal->getGrandTotal()
                    + $invoiceFeeHandling['tax_amount']
                    + $invoiceFeeHandling['amount']
                ),
            ]
        ];
        // Calculate rodunding
        $data['Cart']['Total']['rounding'] = $data['Cart']['Total']['withtax'] - ($data['Cart']['Total']['withouttax'] + $data['Cart']['Total']['tax']);

        return $data;
    }


    /**
     * @return array
     */
	protected function getItemsData()
    {
        $itemsData = [];
        $itemsVisible = $this->getQuote()->getAllVisibleItems();

        foreach ($itemsVisible as $item) {
            $itemsData[] = [
                'quantity' => $item->getQty(),
                'artnr' => $item->getSku(),
                'title' => $item->getName(),
                'aprice' => $this->toCents($item->getPriceInclTax()/(1+$item->getTaxPercent()/100)),
                'taxrate' => $this->calculateItemRate($item),
                'discount' => ($item->getDiscountPercent()),
                'withouttax' => $this->toCents($item->getRowTotal())
            ];
        }

        return $itemsData;
    }

    /**
     * @param $item
     *
     * @return float|int
     */
    protected function calculateItemRate($item)
    {
        $itemTaxRate = $item->getTaxPercent();
        if ($itemTaxRate) {
           return $itemTaxRate;
        }
        return round(($item->getRowTotalInclTax()/$item->getRowTotal()*100)-100,2);
    }

    /**
     * @return string
     */
    public function updateIframe()
    {
        $response = $this->getIframeData();

        if(isset($response['url'])) {
            $this->dataHelper->setSessionData('iframe_url', $response['url']);
            return $response['url'];
        }
        return '';
    }

    /**
     * @return array
     */
    protected function getRequestData()
    {
        $data = [];
        $data['Articles'] = [];
        $data['PaymentData'] = [
            'currency' => 'SEK',
            'language' => 'sv',
            'country' => 'SE',
            'orderid' => $this->getQuote()->getReservedOrderId(),
        ];

        if (!$this->_updateProcessRun) {
            $data['PaymentData']['callbackurl'] = $this->_getUrl('billmatecheckout/callback/callback');
            $data['PaymentData']['accepturl'] = $this->_getUrl('billmatecheckout/success/success/');
            $data['PaymentData']['cancelurl'] = $this->_getUrl('billmatecheckout');

            $data['CheckoutData'] = [
                'windowmode' => 'iframe',
                'sendreciept' => 'yes',
                'terms' => $this->configHelper->getTermsURL(),
                'redirectOnSuccess'=>'true',

            ];

            $privacyPolicyURL = $this->configHelper->getPPURL();
            if ($privacyPolicyURL) {
                $data['CheckoutData']['privacyPolicy'] = $privacyPolicyURL;
            }
            $checkoutMode = $this->configHelper->getCheckoutMode();
            if ($checkoutMode == "business"){
                $data['CheckoutData']['companyView'] = "true";
            }
        }

        $billmateCheckoutId = $this->getBillmateCheckoutId();
        if ($billmateCheckoutId) {
            $data['PaymentData']['number'] = $billmateCheckoutId;
        }

        $shippingAddressTotal = $this->getQuote()->getShippingAddress();
        $discountAmount = $shippingAddressTotal->getDiscountAmount();
        if (abs($discountAmount) > 0) {
            $data['Articles'][] = [
                'quantity' => '1',
                'artnr' => 'discount_code',
                'title' => $shippingAddressTotal->getCouponCode()?
                    $shippingAddressTotal->getCouponCode():
                    __('Discount rules ids: ') . $shippingAddressTotal->getAppliedRuleIds(),
                'aprice' => $this->toCents($discountAmount),
                'taxrate' => '0',
                'discount' => '0',
                'withouttax' => $this->toCents($discountAmount)
            ];
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function getInvoiceFeeHandling()
    {
        return $this->bmInvoiceHandler->getFeeData();
    }

    /**
     * @return int | null
     */
    protected function getBillmateCheckoutId()
    {
        return $this->getSessionData('billmate_checkout_id');
    }


    /**
     * @return $this
     */
    protected function runCheckIsUpdateCheckout()
    {
        if ($this->getBillmateCheckoutId()) {
            $this->_updateProcessRun = true;
            $this->_apiCallMethod = 'updateCheckout';
        }
        return $this;
    }

    /**
     * @return string
     */
    protected function getApiMethod()
    {
        return $this->_apiCallMethod;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function setSessionData($key, $value)
    {
        return $this->dataHelper->setSessionData($key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    protected function getSessionData($key)
    {
        return $this->dataHelper->getSessionData($key);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        return $this->dataHelper->getQuote();
    }

    /**
     * @param $price
     *
     * @return int
     */
    protected function toCents($price)
    {
        return $this->dataHelper->priceToCents($price);
    }

    /**
     * @return  \Magento\Tax\Model\Calculation
     */
    protected function getTaxCalculation()
    {
        return $this->taxCalculation->create();
    }

    /**
     * @return float
     */
    protected function getShippingTaxRate()
    {
        $shippingTaxClass = $this->configHelper->getShippingTaxClass();
        return $this->getTaxRate($shippingTaxClass);
    }

    protected function getTaxRate($taxClassId)
    {
        $currentStore = $this->_storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $taxCalculation = $this->getTaxCalculation();
        $request = $taxCalculation->getRateRequest(
            null,
            null,
            null,
            $currentStoreId
        );

        return $taxCalculation->getRate(
            $request->setProductClassId($taxClassId)
        );
    }

    /**
     * @return Config
     */
    public function getConfigHelper(): Config
    {
        return $this->configHelper;
    }
}
