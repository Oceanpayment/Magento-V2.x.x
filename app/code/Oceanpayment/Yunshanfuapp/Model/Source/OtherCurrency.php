<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Oceanpayment\Yunshanfuapp\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class OtherCurrency implements ArrayInterface {
	
    /**
     * @return array
     */
	public function toOptionArray() {
        return [
            ['value' => '1', 'label' => __('3D Secure')],
            ['value' => '0', 'label' =>__('Sale')]
        ];
    }
}

