<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Oceanpayment\Yunshanfuapp\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class PayMode implements ArrayInterface {
	
    /**
     * @return array
     */
	public function toOptionArray() {
        return [
            ['value' => 'iframe', 'label' => __('Iframe')],
            ['value' => 'redirect', 'label' =>__('Redirect')]
        ];
    }
}

