<?php
namespace Billmate\BillmateCheckout\Controller\BillmateAjax;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class UpdateAddress extends \Magento\Framework\App\Action\Action {

    /**
     * @var PageFactory
     */
	protected $resultPageFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
	private $productRepository;

    /***
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
	protected $helper;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
	protected $orderInterface;

    /**
     * UpdateAddress constructor.
     *
     * @param Context                                          $context
     * @param PageFactory                                      $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository
     * @param \Billmate\BillmateCheckout\Helper\Data           $_helper
     * @param \Magento\Sales\Api\Data\OrderInterface           $order
     * @param \Billmate\BillmateCheckout\Helper\Iframe         $iframeHelper
     */
	public function __construct(Context $context, 
		PageFactory $resultPageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository, 
		\Billmate\BillmateCheckout\Helper\Data $_helper, 
		\Magento\Sales\Api\Data\OrderInterface $order,
        \Billmate\BillmateCheckout\Helper\Iframe $iframeHelper
		){
		$this->resultJsonFactory = $resultJsonFactory;
		$this->resultPageFactory = $resultPageFactory;
	    $this->productRepository = $productRepository;
		$this->helper = $_helper;
        $this->iframeHelper = $iframeHelper;
		$this->orderInterface = $order;
		parent::__construct($context);
	}

    /**
     * @return $this
     */
	public function execute()
    {
		if ($this->getRequest()->getParam('status') == 'Step2Loaded') {
            $billingAddressReq = $this->getBillingAddress();
			if ($billingAddressReq) {
                $this->helper->setBillingAddress($billingAddressReq);
                $this->helper->setSessionData('billmate_country', $billingAddressReq['country_id']);
            }

            $shippingAddressReq = $this->getShippingAddress();
			if ($shippingAddressReq) {
                $this->helper->setShippingAddress($shippingAddressReq);
            }

            $result = $this->resultJsonFactory->create();
			$iframe = $this->iframeHelper->updateIframe();
			$cart = $this->helper->getCartContent();
            $ordermessage = $this->helper->getQuote()->getData('order_comment');
            if (!empty($ordermessage)) {
                $return = array(
                    'iframe' => $iframe,
                    'cart' => $cart,
                    'orderMessage' => $ordermessage
                );
            }
            else {
                $return = array(
                    'iframe' => $iframe,
                    'cart' => $cart
                );
            }
			return $result->setData($return);
		}
	}

    /**
     * @return array
     */
	protected function getBillingAddress()
    {
        $customerAddress = $this->getRequest()->getParam('Customer');
        $billingAddress = $this->getRequest()->getParam('billingAddress',[]);
        $billingFormatted = [];
        if (isset($customerAddress["Billing"]) && $customerAddress["Billing"]) {
            $billingFormatted = [
                'email' => $customerAddress['Billing']['email'],
                'firstname' => $customerAddress['Billing']['firstname'],
                'lastname' => $customerAddress['Billing']['lastname'],
                'street' => $customerAddress['Billing']['street'],
                'city' => $customerAddress['Billing']['city'],
                'country_id' => $customerAddress['Billing']['country'],
                'postcode' => $customerAddress['Billing']['zip'],
                'telephone' => $customerAddress['Billing']['phone']
            ];
        } elseif ($billingAddress) {
            $emailRequested = $this->getRequest()->getParam('email','');
            $_email = (isset($billingAddress['email'])) ? $billingAddress['email'] : $emailRequested;
            $billingFormatted = [
                'email'=> $_email,
                'firstname' => $billingAddress['firstname'],
                'lastname' => $billingAddress['lastname'],
                'street' => $billingAddress['street'],
                'city' => $billingAddress['city'],
                'country_id' => $billingAddress['country'],
                'postcode' => $billingAddress['zip'],
                'telephone' => $billingAddress['phone']
            ];
        }

        return $billingFormatted;
    }

    /**
     * @return array
     */
    protected function getShippingAddress()
    {
        $customerAddress = $this->getRequest()->getParam('Customer');
        $shippingAddress = $this->getRequest()->getParam('shippingAddress',[]);
        $shippingFormatted = [];
        if ($shippingAddress) {
            if (isset($shippingAddress['country'])) {
                $country =  $shippingAddress['country'];
            } else {
                $country = $this->helper->getSessionData('billmate_country');
            }
            $shippingFormatted = [
                'firstname'=> $shippingAddress['firstname'],
                'lastname'=> $shippingAddress['lastname'],
                'street'=> $shippingAddress['street'],
                'city'=> $shippingAddress['city'],
                'country_id'=>$country,
                'postcode'=> $shippingAddress['zip']
            ];
        } elseif ($customerAddress) {
            if (isset($customerAddress['street'])) {
                $shippingFormatted = [
                    'firstname'=> $customerAddress['Shipping']['firstname'],
                    'lastname'=> $customerAddress['Shipping']['lastname'],
                    'street'=> $customerAddress['Shipping']['street'],
                    'city'=> $customerAddress['Shipping']['city'],
                    'country_id'=> $customerAddress['Shipping']['country'],
                    'postcode'=> $customerAddress['Shipping']['zip']
                ];
            }
        }

        return $shippingFormatted;
    }
}