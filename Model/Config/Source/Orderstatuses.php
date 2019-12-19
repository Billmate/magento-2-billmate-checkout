<?php

namespace Billmate\BillmateCheckout\Model\Config\Source;

class Orderstatuses implements \Magento\Framework\Option\ArrayInterface {

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
	protected $statusCollectionFactory;

    /**
     * Orderstatuses constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context                  $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
    ) {
		$this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(){
		$options = $this->statusCollectionFactory->create()->toOptionArray();        
		return $options;
    }
}