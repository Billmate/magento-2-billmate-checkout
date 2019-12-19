<?php
namespace Billmate\BillmateCheckout\Controller\BillmateAjax;

use Magento\Framework\App\Action\Context;

class BillmateAjax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
	protected $helper;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Iframe
     */
	protected $iframeHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
	protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * BillmateAjax constructor.
     *
     * @param Context                                          $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Data\Form\FormKey             $formKey
     * @param \Magento\Checkout\Model\Session                  $_checkoutSession
     * @param \Billmate\BillmateCheckout\Helper\Data           $_helper
     * @param \Billmate\BillmateCheckout\Helper\Iframe         $iframeHelper
     */
	public function __construct(
		Context $context, 
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Checkout\Model\Session $_checkoutSession,
		\Billmate\BillmateCheckout\Helper\Data $_helper,
        \Billmate\BillmateCheckout\Helper\Iframe $iframeHelper,
        \Magento\Checkout\Model\Cart $checkoutCart
		) {
        $this->formKey = $formKey;
		$this->helper = $_helper;
        $this->iframeHelper = $iframeHelper;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->checkoutSession = $_checkoutSession;
        $this->checkoutCart = $checkoutCart;
		parent::__construct($context);
	}

	public function execute() {
		$result = $this->resultJsonFactory->create();
		if ($this->isQuoteExpired()) {
            $errorData = [
                'error' => true,
                'redirect_url' => $this->_url->getUrl( 'checkout/cart')
            ];
            return $result->setData($errorData);
        }

		if ($this->getRequest()->isAjax()) {
            $itemId = $this->getItemIdFromRequest();
			if ($this->getRequest()->getParam('field2') == 'sub') {
				$this->decreaseQty($itemId);
			}elseif ($this->getRequest()->getParam('field2') == 'inc') {
                $this->increaseQty($itemId);
			}elseif ($this->getRequest()->getParam('field2') == 'radio'){
				$this->helper->setShippingMethod($this->getRequest()->getParam('field3'));
			}elseif ($this->getRequest()->getParam('field2') == 'submit'){
				$this->helper->setDiscountCode($this->getRequest()->getParam('field3'));
			}elseif ($this->getRequest()->getParam('field2') == 'del'){
                $this->removeItem($itemId);
			}

            if (!$this->helper->getQuote()->hasItems()) {
			    $errorData = [
			        'error' => true,
                    'redirect_url' => $this->_url->getUrl( 'billmatecheckout')
                ];
                return $result->setData($errorData);
            }

            $cartBlockContent = $this->helper->getCartContent();
            $iframeUrl = $this->iframeHelper->updateIframe();
            $ordermessage = $this->helper->getQuote()->getData('order_comment');
            if (!empty($ordermessage)) {
                $return = array(
                    'iframe' => $iframeUrl,
                    'cart' => $cartBlockContent,
                    'orderMessage' => $ordermessage
                );
            }
            else {
                $return = array(
                    'iframe' => $iframeUrl,
                    'cart' => $cartBlockContent
                );
            }
            return $result->setData($return);
		}
	}

    /***
     * @return bool
     */
    public function isQuoteExpired()
    {
        return !(bool)$this->checkoutCart->getQuote()->getItems();
    }

    /**
     * @param $id
     */
	protected function increaseQty($id)
    {
        $item = $this->helper->getQuote()->getItemById($id);
        if ($item->getId()) {
            $product = $item->getProduct();
            if ($product->getTypeId() == 'configurable') {
                $params = array(
                    'form_key' => $this->formKey->getFormKey(),
                    'product' =>$product->getId(),
                    'super_attribute' => $item->getBuyRequest()->getData()['super_attribute'],
                    'qty' => 1,
                    'price' => $product->getPrice()
                );
            } else {
                $params = array(
                    'form_key' => $this->formKey->getFormKey(),
                    'product' => $product->getId(),
                    'qty' => 1,
                    'price' => $product->getPrice()
                );
            }

            $cart = $this->getCheckoutCart();
            $cart->addProduct($product, $params);
            $cart->save();
        }
    }

    /**
     * @param $id
     */
    protected function removeItem($id)
    {
        $cart = $this->getCheckoutCart();
        $item = $this->helper->getQuote()->getItemById($id);

        if ($item->getId()) {
            $this->helper->getQuote()->removeItem($item->getId());
            $cart->save();
        }
    }

    /**
     * @return \Magento\Checkout\Model\Cart
     */
    protected function getCheckoutCart()
    {
        return $this->checkoutCart;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    protected function decreaseQty($id)
    {
        $cart = $this->getCheckoutCart();
        $item = $this->helper->getQuote()->getItemById($id);
        if ($item->getId()) {

            $qty = $item->getQty();
            if ($qty > 1){
                $item->setQty($qty-1);
                $cart->save();
            } else {
                $this->removeItem($item->getId());
            }
        }

        return $this;
    }

    /**
     * @return bool|int
     */
    protected function getItemIdFromRequest()
    {
        $itemRowRequest = $this->getRequest()->getParam('field3');
        $explodedRow = explode('_', $itemRowRequest);
        if (isset($explodedRow[1])) {
            return $explodedRow[1];
        }
        return false;
    }
}
