<?php
namespace Billmate\BillmateCheckout\Controller;

use Billmate\BillmateCheckout\Controller\FrontCore\AbsModified;

abstract class FrontCore extends AbsModified
{
    /**
     * @return mixed
     */
    protected function getBmRequestData()
    {
        $bmRequestData = $this->getRequest()->getParam('data');
        $bmRequestCredentials = $this->getRequest()->getParam('credentials');

        if ($bmRequestData && $bmRequestCredentials) {
            $postData['data'] = json_decode($bmRequestData, true);
            $postData['credentials'] = json_decode($bmRequestCredentials, true);
            return $postData;
        }

        $jsonBodyRequest = file_get_contents('php://input');
        if ($jsonBodyRequest) {
            return json_decode($jsonBodyRequest, true);
        }
        throw new \Exception('The request does not contain information');
    }
}