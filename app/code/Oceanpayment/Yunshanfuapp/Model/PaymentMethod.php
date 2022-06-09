<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oceanpayment\Yunshanfuapp\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;

class PaymentMethod extends AbstractMethod
{
    const CODE = 'oceanpaymentyunshanfuapp';
    const POST = "[POST to Oceanpayment]";
 
    protected $_code = self::CODE;
    
    protected $_isInitializeNeeded      = true;
    
    protected $_formBlockType = 'Oceanpayment\Yunshanfuapp\Block\Form';
    protected $_infoBlockType = 'Oceanpayment\Yunshanfuapp\Block\Info';
 
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
        return $this->urlBuilder->getUrl('oceanpaymentyunshanfuapp/payment/redirect', ['_secure' => true]);
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


        //支付币种
        $order_currency    = $order->getOrderCurrencyCode();
        //金额
        $order_amount      = sprintf('%.2f', $order->getGrandTotal());

        //判断是否启用3D功能
        if($this->getConfigData('secure/secure_mode') == 1){
            //检验是否需要3D验证
            $validate_arr = $this->validate3D($order_currency, $order_amount, $billing, $shipping);
        }else{
            $validate_arr['terminal'] = $this->getConfigData('terminal');
            $validate_arr['securecode'] = $this->getConfigData('securecode');
        }


        //账户
        $account           = $this->getConfigData('account');
        //终端号
        $terminal          = $validate_arr['terminal'];
        //securecode
        $securecode        = $validate_arr['securecode'];
        //支付方式
        $methods           = 'YunShanFu_APP';
        //订单号
        $order_number      = $orderIncrementId;  
        //返回地址
        $backUrl           = $this->urlBuilder->getUrl('oceanpaymentyunshanfuapp/payment/back', ['_secure' => true,'_nosid' => true]);
        //服务器响应地址
        $noticeUrl         = $this->urlBuilder->getUrl('oceanpaymentyunshanfuapp/payment/notice', ['_secure' => true,'_nosid' => true]);
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
        $cart_info         = 'Magento 2.x';
        //接口版本
        $cart_api          = 'V1.1.0';
        //校验源字符串
        $signsrc           = $account.$terminal.$backUrl.$order_number.$order_currency.$order_amount.$billing_firstName.$billing_lastName.$billing_email.$securecode;
        //sha256加密结果
        $signValue         = hash("sha256",$signsrc);
        //支付页面类型
        $pages             = $this->isMobile();


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
            'pages'=>$pages,
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
        $return_log .= '*****************************************' . "\r\n";
        $return_log = $return_log.file_get_contents( dirname(dirname(__FILE__)) . "/oceanpayment_log/" . $filedate . ".log");     
        $filename   = fopen( dirname(dirname(__FILE__)) . "/oceanpayment_log/" . $filedate . ".log", "r+" );      
        fwrite($filename,$return_log);
        fclose($filename);
        fclose($newfile);
    
    }


    /**
     * 判断是否手机设备
     */
    public function isMobile(){
        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] : '';

        function CheckSubstrs($substrs, $text){
            foreach($substrs as $substr){
                if(false !== strpos($text, $substr)){
                    return true;
                }
            }
            return false;
        }

        $mobile_os_list = array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list = array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');

        $found_mobile = CheckSubstrs($mobile_os_list, $useragent_commentsblock) || CheckSubstrs($mobile_token_list,$useragent);

        if ($found_mobile){
            return 1;   //手机登录
        }else{
            return 0;  //电脑登录
        }
    }


    /**
     * 检验是否需要3D验证
     */
    public function validate3D($order_currency, $order_amount, $billing, $shipping){
    
        //是否需要3D验证
        $is_3d = 0;
        
        //获取3D功能下各个的币种
        $currencies_value_str = $this->getConfigData('secure/secure_currency');
        $currencies_value = explode(';', $currencies_value_str);
        //获取3D功能下各个的金额
        $amount_value_str = $this->getConfigData('secure/secure_amount');
        $amount_value = explode(';', $amount_value_str);
        
        $amountValidate = array_combine($currencies_value, $amount_value);
        
        if($amountValidate){
            //判断金额是否为空
            if(isset($amountValidate[$order_currency])){
                //判断3D金额不为空
                //判断订单金额是否大于3d设定值
                if($order_amount >= $amountValidate[$order_currency]){
                    //需要3D
                    $is_3d = 1;
                }
            }else{
                //其他币种是否需要3D
                if($this->getConfigData('secure/secure_other_currency') == 1){
                    //需要3D
                    $is_3d = 1;
                }

            }
        }

        //获取3D功能下国家列表
        $countries_3d_str = $this->getConfigData('secure/secure_country');
        $countries_3d = explode(',', $countries_3d_str);
        
        //账单国
        $billing_country = $billing->getCountryId();
        //收货国
        $ship_country = $shipping->getCountryId();
    
        //判断账单国是否处于3D国家列表
        if (in_array($billing_country , $countries_3d)){
            $is_3d = 1;
        }
        //判断收货国是否处于3D国家列表
        if (in_array($ship_country , $countries_3d)){
            $is_3d = 1;
        }
    
    
        if($is_3d ==  0){
            $validate_arr['terminal'] = $this->getConfigData('terminal');
            $validate_arr['securecode'] = $this->getConfigData('securecode');
        }elseif($is_3d == 1){
            //3D
            $validate_arr['terminal'] = $this->getConfigData('secure/secure_terminal');
            $validate_arr['securecode'] = $this->getConfigData('secure/secure_securecode');
        }
    
        return $validate_arr;
    
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
