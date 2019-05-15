<?php

namespace Billmate\BillmateCheckout\Model\Payment;

/**
 * Pay In Store payment method model
 */

class BillmateCheckout extends \Magento\Payment\Model\Method\AbstractMethod {

    const PAYMENT_CODE_CHECKOUT = 'billmate_checkout';

    /**
     * @var string
     */
    protected $_code = 'billmate_checkout';

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * @var bool
     */
    protected $_isOffline = false;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * @var Billmate
     */
    protected $billmateProvider;

    /**
     * BillmateCheckout constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory            $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                 $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                                 $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                         $logger
     * @param \Magento\Framework\Module\ModuleListInterface                $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $localeDate
     * @param \Billmate\BillmateCheckout\Helper\Data                       $_helper
     * @param \Billmate\BillmateCheckout\Model\Api\Billmate                $billmateProvider
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Billmate\BillmateCheckout\Helper\Data $_helper,
        \Billmate\BillmateCheckout\Helper\Config $configHelper,
        \Billmate\BillmateCheckout\Model\Api\Billmate $billmateProvider,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $_helper;
        $this->configHelper = $configHelper;
        $this->billmateProvider = $billmateProvider;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float                                $amount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->isAllowedToCallback()) {
            return $this;
        }
        $billmateConnection = $this->getBillMateProvider();

        $order = $payment->getOrder();
        $bmRequestData = $this->getBillmateRequestData($order);

        $billmateConnection->activatePayment($bmRequestData);

        $payment->setTransactionId($order->getData('billmate_invoice_id'));
        $payment->setParentTransactionId($payment->getTransactionId());
        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true, "");
        $transaction->setIsClosed(true);

    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        if (!$this->isAllowedToCallback()) {
            return $this;
        }

        $billmateConnection = $this->getBillMateProvider();

        $order = $payment->getOrder();
        $bmRequestData = $this->getBillmateRequestData($order);

        $billmateConnection->cancelPayment($bmRequestData);

    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        if (!$this->isAllowedToCallback()) {
            return $this;
        }
        $billmateConnection = $this->getBillMateProvider();

        $order = $payment->getOrder();
        $bmRequestData = $this->getBillmateRequestData($order);

        $billmateConnection->cancelPayment($bmRequestData);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float                                $amount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->isAllowedToCallback()) {
            return $this;
        }
        $billmateConnection = $this->getBillMateProvider();

        $order = $payment->getOrder();
        $bmRequestData = $this->getBillmateRequestData($order);
        $bmRequestData['PaymentData']['partcredit'] = 'false';

        $billmateConnection->creditPayment($bmRequestData);

    }

    /**
     * @param $order
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getBillmateRequestData($order)
    {
        $bmRequestData = [];
        if (!empty($order->getData('billmate_invoice_id'))) {
            $bmRequestData["PaymentData"] = [
                "number" => $order->getData('billmate_invoice_id')
            ];
            return $bmRequestData;
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('ID has not been recived from Billmate'));

    }

    /**
     * @return \Billmate\BillmateCheckout\Model\Api\Billmate|Billmate
     */
    protected function getBillMateProvider()
    {
        return $this->billmateProvider;
    }

    /**
     * @return bool
     */
    protected function isAllowedToCallback()
    {
        return $this->configHelper->getPushEvents();
    }
}