/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Oceanpayment_Atome/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, setPaymentMethodAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Oceanpayment_Atome/payment/atome'
            },
            /** Redirect to atome */
            continueToAtome: function () {
                //update payment method information if additional data was changed
                this.selectPaymentMethod();
                setPaymentMethodAction(this.messageContainer);
                return false;
            }
        });
    }
);
