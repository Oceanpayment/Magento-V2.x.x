<?php 

namespace Oceanpayment\Yunshanfuapp\Controller\Payment; 


use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\Data\GroupInterface;
class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    protected $resultPageFactory;
    protected $_paymentMethod;
    protected $_checkoutSession;
    protected $checkout;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Oceanpayment\Yunshanfuapp\Model\PaymentMethod $paymentMethod,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->_paymentMethod = $paymentMethod;
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Credit/Debit Card'));
        return $resultPage;
    }

}


