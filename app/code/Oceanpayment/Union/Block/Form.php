<?php
/**
 * Copyright © 2016 Oceanpayment Design. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oceanpayment\Union\Block;

class Form extends \Magento\Payment\Block\Form
{
    /**
     * Checkmo template
     *
     * @var string
     */
    protected $_supportedInfoLocales = array('fr');
    protected $_defaultInfoLocale = 'en';
    
    protected $_template = 'Oceanpayment_Union::form.phtml';
}
