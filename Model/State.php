<?php


namespace Billmate\BillmateCheckout\Model;

use Magento\Framework\Model\AbstractModel;
use Billmate\BillmateCheckout\Api\Data\StateInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class State
 * @package Billmate\BillmateCheckout\Model
 */
class State extends AbstractModel implements StateInterface, IdentityInterface
{

    const CACHE_TAG = 'sales_order_status_state';

    /**
     * @var string
     */
    protected $_cacheTag = 'sales_order_status_state';
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_state';

    protected function _construct()
    {
        $this->_init('Billmate\BillmateCheckout\Model\ResourceModel\State');
    }

    /**
     * @param int $id
     * @param null $field
     * @return $this|bool
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return false;
        }

        return parent::load($id, $field);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * @param int|mixed $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
