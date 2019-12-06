<?php
namespace Billmate\BillmateCheckout\Block;
 
class Cart extends \Magento\Checkout\Block\Onepage
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
     * Cart constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey             $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider  $configProvider
     * @param \Billmate\BillmateCheckout\Helper\Data           $_helper
     * @param \Billmate\BillmateCheckout\Helper\Iframe         $iframeHelper
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
        array $layoutProcessors = [],
        array $data = []
	) {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
		$this->helper = $_helper;
		$this->iframeHelper = $iframeHelper;
	}

    /**
     * @return string
     */
	public function getAjaxUrl()
    {
		return $this->getUrl('billmatecheckout/billmateajax/billmateajax');
	}

    /**
     * @return string
     */
	public function getJsonConfig()
    {
        $configData = [
            'request_url' => $this->getAjaxUrl()
        ];
        return json_encode($configData);
    }
}