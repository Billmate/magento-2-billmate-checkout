<?php

namespace Billmate\BillmateCheckout\Model\ResourceModel;

/**
 * Class State
 * @package Collector\Iframe\Model\ResourceModel
 */
class State extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var bool
     */
    protected $_useIsObjectNew = false;
    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;


    protected function init()
    {
        parent::init();
        $this->getSelect()->joinleft(
            ['sales_order_status' => $this->getTable('sales_order_status')],
            'main_table.status = sales_order_status.status',
            ['label']
        );
    }

    protected function _construct()
    {
        $this->_init('sales_order_status_state', 'status');
    }

    /**
     * @param $isNew
     */
    public function useIsObjectNew($isNew)
    {
        $this->_useIsObjectNew = $isNew;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param $value
     * @param null $field
     * @return mixed
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {

        return parent::load($object, $value, $field);
    }
}
