<?php
namespace Billmate\BillmateCheckout\Block\Checkout;

class Shipping extends \Magento\Checkout\Block\Onepage
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
    public function displayTaxIncluded()
    {
        return $this->_taxHelper->displayShippingPriceIncludingTax();
    }
}