<?php

namespace Billmate\BillmateCheckout\Setup;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;

class RecurringData implements InstallDataInterface{

    /**
     * Custom Processing Order-Status code
     */
    const ORDER_STATUS_PROCESSING_FULFILLMENT_CODE = 'billmate_pending';

    /**
     * Custom Processing Order-Status label
     */
    const ORDER_STATUS_PROCESSING_FULFILLMENT_LABEL = 'Billmate Pending';

    /**
     * Custom Order-State code
     */
    const ORDER_STATE_CUSTOM_CODE = 'billmate_pending';

    /**
     * Custom Order-Status code
     */
    const ORDER_STATUS_CUSTOM_CODE = 'billmate_pending';

    /**
     * Custom Order-Status label
     */
    const ORDER_STATUS_CUSTOM_LABEL = 'Billmate Pending';

    /**
     * @var CollectionFactory $statusCollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * Status Factory
     *
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * Status Resource Factory
     *
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * Construct
     *
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory,
        CollectionFactory $statusCollectionFactory
    ){
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }


    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $statuses = $this->statusCollectionFactory->create()->toOptionArray();
        $statusExists = false;
        foreach ($statuses as $status){
            if ($status == 'billmate_pending'){
                $statusExists = true;
            }
        }
        if (!$statusExists){
            $this->addNewOrderProcessingStatus();
            $this->addNewOrderStateAndStatus();
        }
    }

    protected function addNewOrderProcessingStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::ORDER_STATUS_PROCESSING_FULFILLMENT_CODE,
            'label' => self::ORDER_STATUS_PROCESSING_FULFILLMENT_LABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {

            return;
        }

        $status->assignState(Order::STATE_PENDING_PAYMENT, false, true);
    }

    /**
     * Create new custom order status and assign it to the new custom order state
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewOrderStateAndStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::ORDER_STATUS_CUSTOM_CODE,
            'label' => self::ORDER_STATUS_CUSTOM_LABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {

            return;
        }

        $status->assignState(self::ORDER_STATE_CUSTOM_CODE, true, true);
    }
}