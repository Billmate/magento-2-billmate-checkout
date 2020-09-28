<?php
namespace Billmate\BillmateCheckout\Controller\Callback;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DB\TransactionFactory;
use Billmate\BillmateCheckout\Model\Order as BillmateOrder;

/**
 * Class Callback
 * @package Billmate\BillmateCheckout\Controller\Callback
 */
class Callback extends \Billmate\BillmateCheckout\Controller\FrontCore
{
    const COUNTRY_ID = 'se';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Billmate\BillmateCheckout\Model\Api\Billmate
     */
    protected $billmateProvider;

    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Billmate\BillmateCheckout\Model\Order
     */
    protected $orderModel;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * Callback constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Billmate\BillmateCheckout\Helper\Data $_helper
     * @param \Billmate\BillmateCheckout\Helper\Config $configHelper
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Model\Service\InvoiceService $_invoiceService
     * @param \Billmate\BillmateCheckout\Model\Api\Billmate $billmateProvider
     * @param TransactionFactory $transactionFactory
     * @param BillmateOrder $orderModel
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Billmate\BillmateCheckout\Helper\Data $_helper,
        \Billmate\BillmateCheckout\Helper\Config $configHelper,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Model\Service\InvoiceService $_invoiceService,
        \Billmate\BillmateCheckout\Model\Api\Billmate $billmateProvider,
        TransactionFactory $transactionFactory,
        \Billmate\BillmateCheckout\Model\Order $orderModel,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->invoiceService = $_invoiceService;
        $this->helper = $_helper;
        $this->configHelper = $configHelper;
        $this->orderInterface = $order;
        $this->billmateProvider = $billmateProvider;
        $this->_transactionFactory = $transactionFactory;
        $this->orderModel = $orderModel;

        parent::__construct($context);
    }

    /**
     * Parent method to execute the Callback
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $jsonResponse = $this->resultJsonFactory->create();
        $requestData = $this->getBmRequestData();
        $hash = $this->getHashCode($requestData);

        try {
            if ($hash != $requestData['credentials']['hash']) {
                throw new \Exception(
                    __('Invalid credentials hash.')
                );
            }
            $values = array(
                'number' => $requestData['data']['number']
            );
            $paymentInfo = $this->billmateProvider->getPaymentinfo($values);

            $quote = $this->quoteCollectionFactory->create()->addFieldToFilter("reserved_order_id", $paymentInfo['PaymentData']['orderid'])->getFirstItem();
            if (!$quote->getData('first_callback_received')){
                $quote->setData('first_callback_received', true);
                $quote->save();

                $jsonResponse->setHttpResponseCode(412);
                $respMessage = "ignoring first callback";
                return $jsonResponse->setData($respMessage);
            }

            $order = $this->helper->getOrderByIncrementId($paymentInfo['PaymentData']['orderid']);
            if (!is_string($order->getIncrementId())) {
                $orderInfo = $this->getOrderInfo($paymentInfo);
                $order_id = $this->orderModel->setOrderData($orderInfo)->create($paymentInfo['PaymentData']['orderid']);
                if (!$order_id) {
                    throw new \Exception(
                        __('An error occurred on the server. Please try to place the order again.')
                    );
                }
                $order = $this->helper->getOrderById($order_id);
            }
            $orderState = "";
            $order->setData(BillmateOrder::BM_INVOICE_ID_FIELD, $requestData['data']['number']);
            if (
                $requestData['data']['status'] == 'Created' ||
                $requestData['data']['status'] == 'Paid' ||
                $requestData['data']['status'] == 'Approved'
            ) {
                $orderState = $this->helper->getApproveStatus();
            } elseif ($requestData['data']['status'] == 'Pending') {
                if ($order->getStatus() == 'billmate_pending') {
                    $orderState = $this->helper->getPendingStatus();
                }
            } else {
                if ($order->getStatus() == 'billmate_pending') {
                    $orderState = $this->helper->getDenyStatus();
                }
            }
            if ($orderState != "") {
                $order->setState($orderState)->setStatus($orderState);
                $order->save();
                $respMessage = __('Order status successfully updated.');
            }
        } catch(\Exception $exception) {
            $this->helper->clearSession();
            $respMessage = $this->resultRedirectFactory->create()->setPath('billmatecheckout/success/error');

        }
        return $jsonResponse->setData($respMessage);
    }

    /**
     * @param $customerAddress
     *
     * @return array
     */
    protected function processShippingAddress($customerAddress)
    {
        $billingAddressReq = $customerAddress['Billing'];
        $billingAddress = array(
            'firstname' => (isset($billingAddressReq['firstname'])) ? $billingAddressReq['firstname'] : '',
            'lastname' => (isset($billingAddressReq['lastname'])) ? $billingAddressReq['lastname'] : '',
            'street' => (isset($billingAddressReq['street'])) ? $billingAddressReq['street'] : '',
            'city' => (isset($billingAddressReq['city'])) ? $billingAddressReq['city'] : '',
            'country_id' => (isset($billingAddressReq['country'])) ? $billingAddressReq['country'] : '',
            'postcode' => (isset($billingAddressReq['zip'])) ? $billingAddressReq['zip'] : '',
            'telephone' => (isset($billingAddressReq['phone'])) ? $billingAddressReq['phone'] : '',
            'email' => (isset($billingAddressReq['email'])) ? $billingAddressReq['email'] : ''
        );

        if (
            isset($customerAddress['Shipping']) &&
            isset($customerAddress['Shipping']['firstname'])
        ) {
            $shippingAddressReq = $customerAddress['Shipping'];
            $customerAddressData = array(
                'firstname' => (isset($shippingAddressReq['firstname'])) ? $shippingAddressReq['firstname'] : '',
                'lastname' => (isset($shippingAddressReq['lastname'])) ? $shippingAddressReq['lastname'] : '',
                'street' => (isset($shippingAddressReq['street'])) ? $shippingAddressReq['street'] : '',
                'city' => (isset($shippingAddressReq['city'])) ? $shippingAddressReq['city'] : '',
                'country_id' => (isset($shippingAddressReq['country'])) ? $shippingAddressReq['country'] : '',
                'postcode' => (isset($shippingAddressReq['zip'])) ? $shippingAddressReq['zip'] : '',
                'telephone' => (isset($shippingAddressReq['phone'])) ? $shippingAddressReq['phone'] : ''
            );
        } else {
            $customerAddressData = $billingAddress;
        }

        $this->helper->setBillingAddress($billingAddress);
        $this->helper->setShippingAddress($customerAddressData);

        return $customerAddressData;
    }

    /**
     * @param $paymentInfo
     *
     * @return array
     */
    protected function getOrderInfo($paymentInfo)
    {
        $customerAddressData = $this->processShippingAddress($paymentInfo['Customer']);
        $orderInfo = array(
            'currency_id'  => (isset($paymentInfo['PaymentData']['currency'])) ? $paymentInfo['PaymentData']['currency'] : '',
            'email'        => (isset($customerAddressData['email'])) ? $customerAddressData['email'] : '',
            'shipping_address' => (isset($customerAddressData)) ? $customerAddressData : array(),
            'items' => array()
        );
        $orderInfo['payment_method_name'] = $paymentInfo['PaymentData']['method_name'];

        $articles = $paymentInfo['Articles'];
        foreach($articles as $article) {
            if ($article['artnr'] == 'discount_code') {
                $this->helper->setSessionData('billmate_applied_discount_code', $article['title']);
            } elseif ($article['artnr'] == 'shipping_code') {
                $this->helper->setShippingMethod($article['title']);
            } else {
                if (strpos($article['artnr'], "discount") === false) {
                    $orderInfo['items'][] = [
                        'product_id' => $article['artnr'],
                        'qty' => $article['quantity'],
                        'price' => (($article['withouttax']/$article['quantity'])/100)
                    ];
                }
            }
        }

        return $orderInfo;
    }

    /**
     * @param $requestData
     *
     * @return string
     */
    protected function getHashCode($requestData)
    {
        $hash = hash_hmac('sha512', json_encode($requestData['data']), $this->configHelper->getBillmateSecret());
        return $hash;
    }
}
