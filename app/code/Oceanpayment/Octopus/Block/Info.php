<?php
/**
 * Copyright © 2016 Oceanpayment Design. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oceanpayment\Octopus\Block;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_payableTo;

    /**
     * @var string
     */
    protected $_mailingAddress;

    /**
     * @var string
     */
    protected $_template = 'Oceanpayment_Octopus::info.phtml';

    
    public function getMethodCode()
    {
        return $this->getInfo()->getMethodInstance()->getCode();
    }

    /**
     * @return string
     */
    public function toPdf()
    {
        //$this->setTemplate('Oceanpayment_Octopus::info/pdf/checkmo.phtml');
        $this->setTemplate('Oceanpayment_Octopus::pdf/info.phtml');
        return $this->toHtml();
    }
}
