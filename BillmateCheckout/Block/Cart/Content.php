<?php
namespace Billmate\BillmateCheckout\Block\Cart;

class Content extends \Magento\Checkout\Block\Onepage
{

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Iframe
     */
    protected $iframeHelper;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $_productConfig = null;


    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $configurationPool;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * Cart constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey             $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider  $configProvider
     * @param \Billmate\BillmateCheckout\Helper\Data           $_helper
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Checkout\Helper\Data                    $checkoutHelper
     * @param array                                            $layoutProcessors
     * @param array                                            $data
     */
    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
		\Billmate\BillmateCheckout\Helper\Data $_helper,
		\Billmate\BillmateCheckout\Helper\Iframe $iframeHelper,
		\Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Billmate\BillmateCheckout\Helper\Config $configHelper,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Tax\Model\Config $taxConfig,
        array $layoutProcessors = [],
        array $data = []
	) {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
		$this->helper = $_helper;
		$this->iframeHelper = $iframeHelper;
		$this->priceHelper = $priceHelper;
        $this->imageBuilder = $imageBuilder;
        $this->configHelper = $configHelper;
        $this->_taxHelper = $taxHelper;
        $this->_productConfig = $productConfig;
        $this->configurationPool = $configurationPool;
        $this->_taxConfig = $taxConfig;

        //ugly hack to remove compilation errors in Magento 2.1.x
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        //end of hack
	}

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
	public function getItems()
    {
        return $this->helper->getItems();
    }

    public function getScopeConfig(){
	    return $this->scopeConfig;
    }

    /**
     * @param $price
     *
     * @return float|string
     */
    public function formatPrice($price, $format = true, $includeContainer = false)
    {
        return $this->priceHelper->currency($price, $format, $includeContainer);
    }

    /**
     * @param $price
     *
     * @return float
     */
    public function getShippingPrice($price)
    {
        return $this->_taxHelper->getShippingPrice(
            $price,
            $this->_taxHelper->displayShippingPriceIncludingTax()
        );
    }

    /**
     * @param       $product
     * @param       $imageId
     * @param array $attributes
     *
     * @return mixed
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @return bool
     */
    public function isEnabledButtons()
    {
        return $this->configHelper->getBtnEnable();
    }

    /**
     * @return bool
     */
    public function isShowAttribute()
    {
        return $this->configHelper->getShowAttribute();
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    public function getProductOptions($item)
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        return $this->configurationPool
            ->getByProductType($item->getProductType())
            ->getOptions($item);
    }

    /**
     * Accept option value and return its formatted view
     *
     * @param mixed $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFormatedOptionValue($optionValue)
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->_productConfig;
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * @return array
     */
    public function getShippingMethodsRates()
    {
        return $this->helper->getShippingMethodsRates();
    }

    /**
     * @return bool
     */
    public function isActiveMethod($methodCode)
    {
        return $this->helper->isActiveShippingMethod($methodCode);
    }

    /**
     * @return bool
     */
    public function hasDiscount()
    {
        $quote = $this->helper->getQuote();
        return ($quote->getSubtotalWithDiscount() < $quote->getSubtotal());
    }

    /**
     * @return float
     */
    public function getDiscountValue()
    {
        return $this->helper->getQuote()
            ->getShippingAddress()
            ->getDiscountAmount();
    }

    /**
     * @return float
     */
    public function getTotalValue()
    {
        return $this->helper->getQuote()
            ->getShippingAddress()
            ->getData('grand_total');
    }

    public function getTaxValue()
    {
        $shippingAddressTotal = $this->helper->getQuote()
            ->getShippingAddress();
        return $shippingAddressTotal->getTaxAmount();
    }

    /**
     * @return float
     */
    public function getShippingValue()
    {
        $shipping = $this->helper->getQuote()
            ->getShippingAddress();
        if ($this->displayShippingIncludeTax()) {
            return $shipping->getShippingInclTax();
        }
        return $shipping->getShippingAmount();
    }

    /**
     * @return bool
     */
    public function displayShippingIncludeTax()
    {
        return $this->_taxConfig->displayCartShippingInclTax();
    }

    /**
     * @return bool
     */
    public function displayPricesIncludeTax()
    {
        return $this->_taxConfig->displayCartPricesInclTax();
    }
}