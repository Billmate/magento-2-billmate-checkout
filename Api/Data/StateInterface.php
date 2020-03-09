<?php

namespace Billmate\BillmateCheckout\Api\Data;

interface StateInterface
{
    const ENTITY_ID = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param string $id
     * @return \Collector\Iframe\Api\Data\StateInterface
     */
    public function setId($id);
}
