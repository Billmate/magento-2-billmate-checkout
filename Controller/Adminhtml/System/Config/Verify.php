<?php
 
namespace Billmate\BillmateCheckout\Controller\Adminhtml\System\Config;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Billmate\BillmateCheckout\Helper\Data;
 
class Verify extends Action {

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Verify constructor.
     *
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     * @param Data        $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
		
    }
}