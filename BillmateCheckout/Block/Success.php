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
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
        return $this->helper->getSessionData('iframe_url');
    }
}