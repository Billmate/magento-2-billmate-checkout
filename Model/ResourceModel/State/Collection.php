<?php

namespace Billmate\BillmateCheckout\Model\ResourceModel\State;

/**
 * Class Collection
 * @package Collector\Iframe\Model\ResourceModel\State
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'status';

    protected function _construct()
    {
        $this->_init(
            'Billmate\BillmateCheckout\Model\State',
            'Billmate\BillmateCheckout\Model\ResourceModel\State'
        );
        $this->_map['fields']['entity_id'] = 'main_table.' . $this->_idFieldName;
    }
}
