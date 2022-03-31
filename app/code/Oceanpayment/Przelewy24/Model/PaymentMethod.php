<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oceanpayment\Przelewy24\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;

class PaymentMethod extends AbstractMethod
{
    const CODE = 'oceanpaymentprzelewy24';
    const POST = "[POST to Oceanpayment]";

    protected $_precisionCurrency = array(
        'BIF','BYR','CLP','CVE','DJF','GNF','ISK','JPY','KMF','KRW',
        'PYG','RWF','UGX','UYI','VND','VUV','XAF','XOF','XPF'
        );

    protected $_code = self::CODE;
    
    protected $_isInitializeNeeded      = true;
    
    protected $_formBlockType = 'Oceanpayment\Przelewy24\Block\Form';
    protected $_infoBlockType = 'Oceanpayment\Przelewy24\Block\Info';
 
    protected $_isGateway                   = false;
    protected $_canAuthorize                = false;
    protected $_canCapture                  = false;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = false;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    protected $_canSaveCc                   = false;
    
    protected $urlBuilder;
    protected $_moduleList;
    protected $checkoutSession;
    protected $_orderFactory;
 
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []){
        $this->urlBuilder = $urlBuilder;
        $this->_moduleList = $moduleList;
        $this->checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data);
    }
    
    /**
     *  Redirect URL
     *
     *  @return   string Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('oceanpaymentprzelewy24/payment/redirect', ['_secure' => true]);
    }

    /**
     *  Gateway URL
     *
     *  @return   string Gateway URL
     */
    public function getGatewayUrl()
    {
        return $this->getConfigData('gateway_url');
    }


    public function canUseForCurrency($currencyCode)
    {
        return true;   
    }
    
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        //$order = $payment->getOrder();

        $state = $this->getConfigData('new_order_status');

        //$state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }
   
    public function getCheckoutParameter()
    {
        $orderIncrementId = $this->checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $productDetails = $this->getProductItems($order->getAllItems());


        
        //账户
        $account           = $this->getConfigData('account');
        //终端号
        $terminal          = $this->getConfigData('terminal');
        //securecode
        $securecode        = $this->getConfigData('securecode');
        //支付币种
        $order_currency    = $order->getOrderCurrencyCode();
        //金额
        $order_amount      = $this->formatAmount($order->getGrandTotal(), $order_currency);//sprintf('%.2f', $order->getGrandTotal());
        //支付方式
        $methods           = 'Przelewy24';
        //订单号
        $order_number      = $orderIncrementId;  
        //返回地址
        $backUrl           = $this->urlBuilder->getUrl('oceanpaymentprzelewy24/payment/back', ['_secure' => true,'_nosid' => true]);
        //服务器响应地址
        $noticeUrl         = $this->urlBuilder->getUrl('oceanpaymentprzelewy24/payment/notice', ['_secure' => true,'_nosid' => true]);
        //备注
        $order_notes       = $orderIncrementId;
        //账单人名
        $billing_firstName = $this->OceanHtmlSpecialChars($billing->getFirstname());
        //账单人姓
        $billing_lastName  = $this->OceanHtmlSpecialChars($billing->getLastname());
        //账单人email
        $billing_email     = $this->OceanHtmlSpecialChars($order->getCustomerEmail());
        //账单人电话
        $billing_phone     = $billing->getTelephone();
        //账单人国家
        $billing_country   = $billing->getCountryId();
        //账单人州(可不提交)
        $billing_state     = $billing->getRegionCode();
        //账单人城市
        $billing_city      = $billing->getCity();
        //账单人地址
        $billing_address   = implode(' ', $billing->getStreet());
        //账单人邮编
        $billing_zip       = $billing->getPostcode();       
        //收货人地址信息
        //收货人名
        $ship_firstName    = $shipping->getFirstname();
        //收货人姓
        $ship_lastName     = $shipping->getLastname();
        //收货人手机
        $ship_phone        = $shipping->getTelephone();
        //收货人国家
        $ship_country      = $shipping->getCountryId();
        //收货人州
        $ship_state        = $shipping->getRegionCode();
        //收货人城市
        $ship_city         = $shipping->getCity();
        //收货人地址
        $ship_addr         = implode(' ', $shipping->getStreet());
        //收货人邮编
        $ship_zip          = $shipping->getPostcode();
        //产品名称
        $productName       = $productDetails['productName'];
        //产品SKU
        $productSku        = $productDetails['productSku'];
        //产品数量
        $productNum        = $productDetails['productNum'];
        //产品单价
        $productPrice      = $productDetails['productPrice'];
        //网店程序类型
        $isMobile          = $this->isMobile() ? 'Mobile' : 'PC';
        $cart_info         = 'Magento 2.x|V1.0.0|'.$isMobile;
        //接口版本
        $cart_api          = '';
        //校验源字符串
        $signsrc           = $account.$terminal.$backUrl.$order_number.$order_currency.$order_amount.$billing_firstName.$billing_lastName.$billing_email.$securecode;
        //sha256加密结果
        $signValue         = hash("sha256",$signsrc);



        $parameter = array('account'=>$account,
            'terminal'=>$terminal,
            'order_number'=>$order_number,
            'order_currency'=>$order_currency,
            'order_amount'=>$order_amount,
            'backUrl'=>$backUrl,
            'noticeUrl'=>$noticeUrl,
            'order_notes'=>$order_notes,
            'methods'=>$methods,
            'signValue'=>$signValue,
            'billing_firstName'=>$billing_firstName,
            'billing_lastName'=>$billing_lastName,
            'billing_email'=>$billing_email,
            'billing_phone'=>$billing_phone,
            'billing_country'=>$billing_country,
            'billing_state'=>$billing_state,
            'billing_city'=>$billing_city,
            'billing_address'=>$billing_address,
            'billing_zip'=>$billing_zip,
            'ship_firstName'=>$ship_firstName,
            'ship_lastName'=>$ship_lastName,
            'ship_phone'=>$ship_phone,
            'ship_country'=>$ship_country,
            'ship_state'=>$ship_state,
            'ship_city'=>$ship_city,
            'ship_addr'=>$ship_addr,
            'ship_zip'=>$ship_zip,
            'productName'=>$productName,
            'productSku'=>$productSku,
            'productNum'=>$productNum,
            'productPrice'=>$productPrice,
            'cart_info'=>$cart_info,
            'cart_api'=>$cart_api,
        );


        //记录提交日志
        $this->postLog(self::POST, $parameter);


        return $parameter;
    }
    
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        
        if (parent::isAvailable($quote) && $quote){
            return true;
        }
        return false;
    }
   

    /**
     * post log
     */
    public function postLog($logType, $data){
    
        $filedate   = date('Y-m-d');
        $newfile    = fopen(  dirname(dirname(__FILE__)) . "/oceanpayment_log/" . $filedate . ".log", "a+" );      
        $return_log = date('Y-m-d H:i:s') . $logType . "\r\n";  
        foreach ($data as $k=>$v){
            $return_log .= $k . " = " . $v . "\r\n";
        }   
        $return_log = $return_log.file_get_contents( dirname(dirname(__FILE__)) . "/oceanpayment_log/" . $filedate . ".log");     
        $filename   = fopen( dirname(dirname(__FILE__)) . "/oceanpayment_log/" . $filedate . ".log", "r+" );      
        fwrite($filename,$return_log);
        fclose($filename);
        fclose($newfile);
    
    }


    /**
     * 格式化金额
     */
    function formatAmount($order_amount, $order_currency){
     
        if(in_array($order_currency, $this->_precisionCurrency)){
            $order_amount = round($order_amount, 0);
        }else{
            $order_amount = round($order_amount, 2);
        }
        
        return $order_amount;

    }

    /**
     * 获取订单详情
     */
    function getProductItems($AllItems){
    
        $productDetails = array();
        $productName = array();
        $productSku = array();
        $productNum = array();
        $productPrice = array();
        
        foreach ($AllItems as $item) {
            $productName[] = $item->getName();
            $productSku[] = $item->getSku();
            $productNum[] = number_format($item->getQtyOrdered());
            $productPrice[] = sprintf('%.2f', $item->getPrice());
        }
        
        $productDetails['productName'] = implode(';', $productName);
        $productDetails['productSku'] = implode(';', $productSku);
        $productDetails['productNum'] = implode(';', $productNum);
        $productDetails['productPrice'] = implode(';', $productPrice);
        
        return $productDetails;
    
    }
    
    

    /**
     * 检验是否移动端
     */
    function isMobile(){
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 判断手机发送的客户端标志
        if (isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = array (
                    'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel',
                    'lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm',
                    'operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return true;
            }
        }
        // 判断协议
        if (isset ($_SERVER['HTTP_ACCEPT'])){
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
                return true;
            }
        }
        return false;
    }

    /**
     * 钱海支付Html特殊字符转义
     */
    function OceanHtmlSpecialChars($parameter){

        //去除前后空格
        $parameter = trim($parameter);

        //转义"双引号,<小于号,>大于号,'单引号
        $parameter = str_replace(array("<",">","'","\""),array("&lt;","&gt;","&#039;","&quot;"),$parameter);
        
        return $parameter;

    }

}
