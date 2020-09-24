<?php
namespace Billmate\BillmateCheckout\Controller\Success;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\Session as CheckoutSession;
use Billmate\BillmateCheckout\Model\Order as BillmateOrder;

class Success extends \Billmate\BillmateCheckout\Controller\FrontCore
{

    /**
     * @var PageFactory
     */
	protected $resultPageFactory;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
	protected $helper;

    /**
     * @var CheckoutSession
     */
	protected $checkoutSession;

    /**
     * @var \Magento\Framework\Event\Manager
     */
	protected $eventManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Billmate\BillmateCheckout\Model\Order
     */
    protected $orderModel;

    /**
     * @var \Billmate\BillmateCheckout\Model\Api\Billmate
     */
    protected $billmateProvider;

    protected $quoteFactory;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		\Magento\Framework\Event\Manager $eventManager,
		\Billmate\BillmateCheckout\Helper\Data $_helper,
		CheckoutSession $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Billmate\BillmateCheckout\Model\Order $orderModel,
        \Billmate\BillmateCheckout\Model\Api\Billmate $billmateProvider,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
	) {
	    $this->quoteFactory = $quoteFactory;
		$this->eventManager = $eventManager;
		$this->resultPageFactory = $resultPageFactory;
		$this->checkoutSession = $checkoutSession;
		$this->helper = $_helper;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->successValidator = $successValidator;
        $this->orderModel = $orderModel;
        $this->billmateProvider = $billmateProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sucesspage.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->helper->setSessionData('billmate_checkout_id',null);
        $logger->info("test");

        try{
            $logger->info("just innanför try");

            $requestData = $this->getBmRequestData();
            $logger->info("innan values");

            $values = array(
                "number" => $requestData['data']['number']
            );

            $logger->info("innan paymentinfo");
            $paymentInfo = $this->billmateProvider->getPaymentinfo($values);

            if (!$this->helper->getSessionData('bm-inc-id')) {
                $logger->info("fanns ingen sessions data");

                $billmateEmail = ($this->helper->getSessionData('billmate_email')) ? $this->helper->getSessionData('billmate_email') : $paymentInfo['Customer']['Billing']['email'];
                $billmateShipping = ($this->helper->getSessionData('billmate_billing_address')) ? $this->helper->getSessionData('billmate_billing_address') : $paymentInfo['Customer']['Billing']['street'];
                $billmateStatus = ($requestData['data']['status']) ? $requestData['data']['status'] : $paymentInfo['PaymentData']['status'];
                if (!$billmateShipping) {
                    $billmateShipping = ($paymentInfo['Customer']['Shipping']['street']) ? $paymentInfo['Customer']['Shipping']['street'] : $paymentInfo['Customer']['Billing']['street'];
                }

                $orderData = array(
                    'email' => $billmateEmail,
                    'shipping_address' => $billmateShipping,
                    'payment_method_name' => $paymentInfo['PaymentData']['method_name'],
                    'payment_method_bm_code' => $paymentInfo['PaymentData']['method'],
                    'payment_bm_status' => $billmateStatus,
                );
                
            }
            else{
                $logger->info("fanns redan sessionsdata");

            }
        } catch (\Exception $e){

            $logger->info("e - message". $e->getMessage());
            $logger->info("e - file". $e->getFile());
            $logger->info("e - line". $e->getLine());
            $this->helper->addLog([
                'note' => 'Could not redirect customer to store order confirmation page',
                '__FILE__' => __FILE__,
                '__CLASS__' => __CLASS__,
                '__FUNCTION__' => __FUNCTION__,
                '__LINE__' => __LINE__,
                'exception.message' => $e->getMessage(),
                'exception.file' => $e->getFile(),
                'exception.line' => $e->getLine(),
            ]);


            if($this->orderModel->isOrderSent() == 1){
                $logger->info($this->orderModel->isOrderSent(). "  Odern har blivit skickad från error till magento admin");
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');

            }
            $this->helper->clearSession();

            //Här gör vi en cancel
            $values = array(
                "number" => $requestData['data']['number']
            );
            $this->billmateProvider->cancelPayment($values);
            $order = $this->helper->getOrderByIncrementId($this->helper->getSessionData('bm-inc-id'));
            $order->delete();

           return $this->resultRedirectFactory->create()->setPath('billmatecheckout/success/error');
        }

       

        if (!$this->helper->getSessionData('bm-inc-id')){
            $orderId = $this->orderModel->setOrderData($orderData)->create();
            $logger->info("orderid 0 ifall ingen order är lagd".$orderId);

                if (!$orderId) {
                    //Här gör vi en cancel
                    $values = array(
                        "number" => $requestData['data']['number']
                    );
                    $this->billmateProvider->cancelPayment($values);
                    $order = $this->helper->getOrderByIncrementId($this->helper->getSessionData('bm-inc-id'));
                    $order->delete();
                    $logger->info("finns inget order ID här kan det bli så att ordern skickas till magento");

                    $this->helper->clearSession();

                    return $this->resultRedirectFactory->create()->setPath('billmatecheckout/success/error');
                    throw new \Exception(
                        __('An error occurred on the server. Please try to place the order again.')
                    );
                }

            $this->helper->setSessionData('bm_order_id', $orderId);
        }
            $order = $this->helper->getOrderByIncrementId($this->helper->getSessionData('bm-inc-id'));
            $this->registry->register('bm-inc-id', $this->helper->getSessionData('bm-inc-id'));

            $orderId = $order->getId();
                    
            $order->setData(BillmateOrder::BM_INVOICE_ID_FIELD, $requestData['data']['number']);
            $order->save();

            $this->eventManager->dispatch(
                    'checkout_onepage_controller_success_action',
                    ['order_ids' => [$order->getId()]]
            );
            $this->quoteFactory->create()->load($order->getQuoteId())->setIsActive(0)->save();

            $this->checkoutSession->setLastSuccessQuoteId($this->helper->getQuote()->getId());
            $this->checkoutSession->setLastQuoteId($this->helper->getQuote()->getId());
            $this->checkoutSession->setLastOrderId($orderId);

            $logger->info($this->orderModel->isOrderSent(). "  Odern har blivit skickad till magento admin");

            //$this->helper->clearSession();

            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }
}
