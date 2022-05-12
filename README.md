<h2>Overview</h4>
Oceanpayment supports mainstream open-source payment plug-ins, such as Magento, WordPress, OpenCart, PrestaShop, and Zen Cart, which are easy to install and save development costs and resources. 
<h2>Plug-in installation above 2.0.</h2>
<h4>Introduce</h4>
Magento is a professional open source e-commerce system. Magento is designed to be very flexible, with a modular architecture system and functions. It is easy to integrate seamlessly with third-party application systems. It is oriented to enterprise-level applications and can handle various needs and build a multi-purpose and applicable e-commerce website.
<ul>
  <li>Supports Card Payments and Alternative Payments embedded plug-ins.</li>
  <li>Support email sending.</li>
</ul>
<h4>Plug-in installation</h4>
<ol>
    <li>Overwrite the downloaded file to the root directory of the magento website.</li>
    <li>Run php bin/magento setup:upgrade in the root directory of the website and wait for all modules to be loaded.</li>
    <li>Clear the background cache System->Cache Management.</li>
    <li>Go to Configuration Stores->Configuration->Sales->Payment Methods.</li>
</ol>
<table>
  <tr>
    <th>Configuration</th>
    <th>Options/values</th>  
  </tr>
  <tr>
    <td>Enable</td>
    <td>Yes</td>
  </tr>
  <tr>
    <td>Title</td>
    <td>Przelewy24</td>
  </tr> 
  <tr>
    <td>Account</td>
    <td>Provide by Oceanpayment technical support.</td>
  </tr>
  <tr>
    <td>Terminal</td>
    <td>Provide by Oceanpayment technical support.</td>
  </tr>
  <tr>
    <td>SecureCode</td>
    <td>Provide by Oceanpayment technical support.</td>
  </tr>
  <tr>
    <td>Gataway URL</td>
    <td>Production environment：https://secure.oceanpayment.com/gateway/service/pay<br>Sandbox environment：https://test-secure.oceanpayment.com/gateway/service/pay</td>
  </tr>
  <tr>
    <td>Pay Mode</td>
    <td>Redirect:Redirect to open payment page<br>Iframe:iframe payment page.</td>
  </tr>
  <tr>
    <td>New Order Status</td>
    <td>On Hold</td>
  </tr>
  <tr>
    <td>Approved Order Status</td>
    <td>Processing</td>
  </tr>
  <tr>
    <td>Failure Order Status</td>
    <td>Canceled</td>
  </tr>
  <tr>
    <td>Pending Order Status</td>
    <td>Pending</td>
  </tr>
  <tr>
    <td>Invoice When Complete</td>
    <td>Yes</td>
  </tr>
  <tr>
    <td>Payment from Applicable Countries</td>
    <td>All Allowed Countries</td>
  </tr>
</table>
