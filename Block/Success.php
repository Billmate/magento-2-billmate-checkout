<?php
namespace Billmate\BillmateCheckout\Block;
 
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $checkoutSession;

    /**
     * Success constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Billmate\BillmateCheckout\Helper\Data           $_helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Billmate\BillmateCheckout\Helper\Data $_helper,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $_checkoutSession;
		$this->helper = $_helper;
        $this->registry = $registry;
	}

    /**
     * @return int
     */
	public function getLastOrderIncId()
    {
        return $this->registry->registry('bm-inc-id');
    }

    /**
     * @return string
     */
    public function getSucessUrl()
    {
        $iframedata = $this->helper->getSessionData('iframe_url');
        $this->clearSession();
        return $iframedata;
    }

    public function clearSession(){
        $this->checkoutSession->clearStorage();
        $this->checkoutSession->clearQuote();
        $this->helper->setSessionData('bm-inc-id', null);
        $this->helper->setSessionData('billmate_email', null);
        $this->helper->setSessionData('billmate_billing_address', null);
    }
}