<?php
namespace Billmate\BillmateCheckout\Model\Payment\Handling;

/**
 * Class Invoice
 * @package Billmate\BillmateCheckout\Model\Handling
 */
class Invoice
{
    const BM_INVOICE_FEE_CODE = 'bm_invoice_fee';

    /**
     * Invoice constructor.
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Billmate\BillmateCheckout\Helper\Config   $configHelper
     * @param \Magento\Tax\Model\CalculationFactory      $taxCalculation
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Billmate\BillmateCheckout\Helper\Config $configHelper,
        \Magento\Tax\Model\CalculationFactory $taxCalculation
    ) {
        $this->_storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->taxCalculation = $taxCalculation;
    }

    /**
     * @return mixed
     */
    public function getFeeData()
    {
        $invoiceFeeHandling['amount'] = 0;
        $invoiceFeeHandling['tax_amount'] = 0;
        $invoiceFeeHandling['rate'] = 0;

        $invoiceFee = $this->configHelper->getInvoiceFee();
        $enabled = $this->configHelper->getEnable();
        if ($enabled && $invoiceFee) {
            $invoiceFeeTax = $this->configHelper->getInvoiceTaxClass();
            $invoiceFeeRate =  $this->getTaxRate($invoiceFeeTax);

            $invoiceFeeHandling['amount'] = $invoiceFee;
            $invoiceFeeHandling['tax_amount'] = (($invoiceFee) * ($invoiceFeeRate / 100));
            $invoiceFeeHandling['rate'] = $invoiceFeeRate;
        }
        return $invoiceFeeHandling;
    }

    /**
     * @param $taxClassId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getTaxRate($taxClassId)
    {
        $currentStore = $this->_storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $taxCalculation = $this->getTaxCalculation();
        $request = $taxCalculation->getRateRequest(
            null,
            null,
            null,
            $currentStoreId
        );

        return $taxCalculation->getRate(
            $request->setProductClassId($taxClassId)
        );
    }

    /**
     * @return  \Magento\Tax\Model\Calculation
     */
    protected function getTaxCalculation()
    {
        return $this->taxCalculation->create();
    }
}
