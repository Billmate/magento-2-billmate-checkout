<?php
namespace Billmate\BillmateCheckout\Test\Controller\FrontCore;

abstract class AbsInterface extends \Magento\Framework\App\Action\Action
    implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(
        \Magento\Framework\App\RequestInterface $request
    ): ?\Magento\Framework\App\Request\InvalidRequestException {
        return true;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
    }
}
