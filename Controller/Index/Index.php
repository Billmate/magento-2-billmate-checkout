<?php

namespace Billmate\BillmateCheckout\Controller\Index;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Billmate\BillmateCheckout\Controller\Index
 */
class Index extends \Magento\Checkout\Controller\Index\Index
{
    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param CustomerRepositoryInterface                        $customerRepository
     * @param AccountManagementInterface                         $accountManagement
     * @param \Magento\Framework\Registry                        $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface       $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator     $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory              $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface         $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory         $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory       $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory    $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory   $resultJsonFactory
     * @param \Billmate\BillmateCheckout\Helper\Config           $configHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Billmate\BillmateCheckout\Helper\Config $configHelper
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );

        $this->configHelper = $configHelper;
    }

    public function execute()
    {
        if (!$this->configHelper->getEnable()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        }

        return parent::execute();
    }
}