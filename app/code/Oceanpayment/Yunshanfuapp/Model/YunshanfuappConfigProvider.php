<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oceanpayment\Yunshanfuapp\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Payment\Helper\Data as PaymentHelper;

class YunshanfuappConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var string[]
     */
    /*protected $methodCodes = [
        Config::METHOD_WPP_BML,
        Config::METHOD_WPP_PE_EXPRESS,
        Config::METHOD_WPP_EXPRESS,
        Config::METHOD_WPP_PE_BML
    ];*/

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;
    
    protected $checkoutSession;

    /**
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        //ConfigFactory $configFactory,
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        \Magento\Checkout\Model\Session $checkoutSession,
        PaymentHelper $paymentHelper
    ) {
        $this->localeResolver = $localeResolver;
        //$this->config = $configFactory->create();
        $this->currentCustomer = $currentCustomer;
        $this->paymentHelper = $paymentHelper;
        $this->checkoutSession = $checkoutSession;
        $code = 'oceanpaymentyunshanfuapp';
        $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $code = 'oceanpaymentyunshanfuapp';
        $config = [];
        
        if ($this->methods[$code]->isAvailable($this->checkoutSession->getQuote())) {
            $config = [];
            $config['payment'] = [];
            $config['payment']['yunshanfuapp']['redirectUrl'] = [];
            $config['payment']['yunshanfuapp']['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
        }
        
        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getOrderPlaceRedirectUrl();
    }


}
