<?php
/**
 * @author 
 * @copyright Copyright (c) 2017
 * @package 
 */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Billmate_BillmateCheckout',
    __DIR__
);
if (interface_exists('Magento\Framework\App\CsrfAwareActionInterface')) {
    class_alias('Billmate\BillmateCheckout\Test\Controller\FrontCore\AbsInterface', 'Billmate\BillmateCheckout\Controller\FrontCore\AbsModified');
} else {
    class_alias('Billmate\BillmateCheckout\Controller\FrontCore\Abs', 'Billmate\BillmateCheckout\Controller\FrontCore\AbsModified');
}