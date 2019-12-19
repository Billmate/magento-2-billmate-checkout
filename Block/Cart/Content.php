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
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $groupedProductClass;

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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $_productFactory,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $_groupedProductClass,
        array $layoutProcessors = [],
        array $data = []
	) {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
		$this->helper = $_helper;
		$this->iframeHelper = $iframeHelper;
        $this->groupedProductClass = $_groupedProductClass;
        $this->productFactory = $_productFactory;
		$this->priceHelper = $priceHelper;
        $this->imageBuilder = $imageBuilder;
        $this->configHelper = $configHelper;
        $this->_taxHelper = $taxHelper;
        $this->_productConfig = $productConfig;
        $this->configurationPool = $configurationPool;
        $this->_taxConfig = $taxConfig;
        $this->scopeConfig = $scopeConfig;
        $this->imageHelper = $imageHelper;
	}

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
	public function getItems()
    {
        return $this->helper->getItems();
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
     * @param       $cartItem
     * @param       $imageId
     * @param array $attributes
     *
     * @return mixed
     */
    public function getImage($cartItem, $imageId = null, $attributes = [])
    {
        if ($imageId == null) {
            $product = $cartItem->getProduct();
            $useConfigurableParentImage = $this->scopeConfig->getValue(
                'checkout/cart/configurable_product_image',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $useGroupedParentImage = $this->scopeConfig->getValue(
                'checkout/cart/grouped_product_image',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($cartItem->getProductType() == 'configurable') {
                if ($useConfigurableParentImage == \Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE) {
                    $image = $this->imageHelper->init(
                        $product,
                        'product_page_image_small'
                    )->setImageFile($product->getFile())->resize(80, 80)->getUrl();
                } else {
                    $image = $this->imageHelper->init(
                        $cartItem->getChildren()[0]->getProduct(),
                        'product_page_image_small'
                    )->setImageFile($cartItem->getChildren()[0]->getProduct()->getFile())->resize(80, 80)->getUrl();
                }
            } else if ($cartItem->getProductType() == 'grouped') {
                if ($useGroupedParentImage == \Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE) {
                    $groupedProductId = $this->groupedProductClass->getParentIdsByChild($product->getId())[0];
                    $groupedProduct = $this->productFactory->create()->load($groupedProductId);
                    $image = $this->imageHelper->init(
                        $groupedProduct,
                        'product_page_image_small'
                    )->setImageFile($groupedProduct->getFile())->resize(80, 80)->getUrl();
                } else {
                    $image = $this->imageHelper->init(
                        $product,
                        'product_page_image_small'
                    )->setImageFile($product->getFile())->resize(80, 80)->getUrl();
                }
            } else {
                $image = $this->imageHelper->init(
                    $product,
                    'product_page_image_small'
                )->setImageFile($product->getFile())->resize(80, 80)->getUrl();
            }
            return $image;
        }
        return $this->imageBuilder->setProduct($cartItem->getProduct())
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

    /**
     * @return float
     */
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