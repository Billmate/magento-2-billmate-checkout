<?php

namespace Billmate\BillmateCheckout\Model\Config\Source;

use Magento\Sales\Model\Order as StateConst;

class AcceptStatus implements \Magento\Framework\Option\ArrayInterface
{

    protected $statusArray = [];
    protected $allowedState = [
        StateConst::STATE_NEW,
        StateConst::STATE_PROCESSING,
        StateConst::STATE_PENDING_PAYMENT,
    ];
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected $statusCollection;

    /**
     * @var
     */
    protected $statusToStateCollection;

    /**
     * AcceptStatus constructor.
     * @param \Billmate\BillmateCheckout\Model\ResourceModel\State\CollectionFactory $statusToStateCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection
     */
    public function __construct(
        \Billmate\BillmateCheckout\Model\ResourceModel\State\CollectionFactory $statusToStateCollection,
        \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection
    ) {
        $this->statusCollection = $statusCollection;
        $this->statusToStateCollection = $statusToStateCollection->create();
        $this->statusToStateCollection->addFieldToFilter('state', ['in' => $this->allowedState]);
    }

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (count($this->statusArray) == 0) {
            $statusLabels = $this->statusCollection->toOptionHash();
            foreach ($this->statusToStateCollection as $item) {
                if ($item->getStatus() == 'billmate_pending'){
                    continue;
                }
                $this->statusArray[$item->getStatus()] = __($statusLabels[$item->getStatus()]);
            }
        }
        return $this->statusArray;
    }

    public function toArray()
    {
        return $this->toOptionArray();
    }
}
