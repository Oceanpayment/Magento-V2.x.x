<?php

/**
 *                                                                                                                                                                        ######
 *                                                                                                                                                                        ######
 *  ###########    ###########    ###########    ###########    ###########   ############    ###########   ###### ######   ###########    ###########    ###########   #############
 * #############  #############  #############  #############  #############  #############  #############  ###### ######  #############  #############  #############  #############
 * ###### ######  ###### ######  ###### ######         ######  ###### ######  ###### ######         ######  ###### ######  #### ### ####  ###### ######  ###### ######  #############
 * ###### ######  ######         #############  #############  ###### ######  ###### ######  #############  ###### ######  #### ### ####  #############  ###### ######    ######
 * ###### ######  ###### ######  ######         ###### ######  ###### ######  ###### ######  ###### ######  ###### ######  #### ### ####  ######         ###### ######    ######
 * #############  #############  #############  #############  ###### ######  #############  #############  #############  #### ### ####  #############  ###### ######    ##########
 *  ###########    ###########    ###########    ###########   ###### ######  ############    ###########   #############  #### ### ####   ###########   ###### ######    ##########
 *                                                                            ######                               ######
 *                                                                            ######                        #############
 *                                                                            ######                        ############
 * 
 * 
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oceanpayment\Klarna\Block\Payment;


class Redirect extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    protected $_paymentMethod;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Oceanpayment\Klarna\Model\PaymentMethod $paymentMethod,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_paymentMethod = $paymentMethod;
        $this->_isScopePrivate = true;
    }



    public function getCheckoutForm(){

        $parameter = $this->_paymentMethod->getCheckoutParameter();
        $gatewayUrl = $this->_paymentMethod->getGatewayUrl();
        
        $formHTML = '';
        $formHTML .= '<form action="'.$gatewayUrl.'" name="payment_checkout" id="payment_checkout">';
        foreach ($parameter as $field => $value) {
            $formHTML .= "<input type='hidden' name='".$field."' value='".$value."' >";
        }
        $formHTML .= '</form>';
        
        // echo "<pre>";
        // print_r($formHTML);die();

        return $formHTML;
    }

    public function getPayMode(){
        $billing_country = $this->_paymentMethod->getBillingCountry();
        $pay_mode = $this->_paymentMethod->getConfigData('pay_mode');
        if($pay_mode == 'redirect' || in_array($billing_country,array('BE','FR','IT','ES','CH'))){
            return 'redirect';
        }else{
            return 'iframe';
        }

    }

}
