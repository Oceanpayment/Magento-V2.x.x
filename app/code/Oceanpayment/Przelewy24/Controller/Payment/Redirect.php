<?php 

namespace Oceanpayment\Przelewy24\Controller\Payment; 


use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Api\CartManagementInterface;

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
    protected $cartManagement;
    protected $orderRepository;
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Oceanpayment\Przelewy24\Model\PaymentMethod $paymentMethod,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CartManagementInterface $cartManagement
    ) {
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->_paymentMethod = $paymentMethod;
        $this->_checkoutSession = $checkoutSession;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;
        $this->_scopeConfig = $scopeConfig;
        
    }

    
    public function execute()
    {

        if($this->_checkoutSession->getQuote()->getId() != null){
            $orderId = $this->cartManagement->placeOrder($this->_checkoutSession->getQuote()->getId());
            $order = $this->orderRepository->get($orderId);
            if ($order){
                // $order->setState($this->_scopeConfig->getValue('payment/oceanpaymentprzelewy24/new_order_status'));
                // $order->setStatus($this->_scopeConfig->getValue('payment/oceanpaymentprzelewy24/new_order_status'));
                $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $order->save();
            }
        }else{
            $this->messageManager->addErrorMessage("Order Error");
            $this->_redirect('checkout/cart');
        }
        

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Przelewy24'));
        return $resultPage;

    }

}


