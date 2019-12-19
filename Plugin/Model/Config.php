<?php
namespace Billmate\BillmateCheckout\Plugin\Model;

use Magento\Framework\App\Action\Context;

class Config {

    const CONFIG_PATH_TERMS_URL = 'groups/billmate_checkout/groups/general/fields/terms_url/value';

    const CONFIG_PATH_ENABLE = 'groups/billmate_checkout/groups/general/fields/enable/value';

    const DISABLE_VALUE = 0;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Config constructor.
     *
     * @param \Billmate\BillmateCheckout\Helper\Config $configHelper
     * @param Context                                  $context
     */
    public function __construct(
        \Billmate\BillmateCheckout\Helper\Config $configHelper,
        Context $context
    ) {
        $this->configHelper = $configHelper;
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * @param $configModel
     */
    public function beforeSave($configModel)
    {
        $privacyPolicy = $configModel->getData(self::CONFIG_PATH_TERMS_URL);
        $isEnable = $configModel->getData(self::CONFIG_PATH_ENABLE);
        if ($isEnable && !$privacyPolicy) {
            $this->messageManager->addWarning(__('The option "Store Terms" is required for Billmate Checkout'));
        }
    }
}