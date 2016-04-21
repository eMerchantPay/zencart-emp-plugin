<?php
/*
 * Copyright (C) 2016 eMerchantPay Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      eMerchantPay
 * @copyright   2016 eMerchantPay Ltd.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

if (!class_exists('EMerchantPay\Direct\TemplateManager')) {
    return;
}

if (\EMerchantPay\Direct\Settings::getIsAvailableOnCheckoutPage() &&
    \EMerchantPay\Direct\Settings::getShouldUseIntegratedPaymentTemplate()
) {
    echo \EMerchantPay\Direct\TemplateManager::getCardJSContent(
        array(
            'form' => 'form[name="checkout_payment"]',
            'container' => '#payment-method-emerchantpay-direct .card-wrapper',
            'formSelectors' => array(
                'nameInput' => 'input[name="' . EMERCHANTPAY_DIRECT_CODE . '_cc_owner"]',
                'numberInput' => 'input[name="' . EMERCHANTPAY_DIRECT_CODE . '_cc_number"]',
                'cvcInput' =>'input[name="' . EMERCHANTPAY_DIRECT_CODE . '_cc_cvv"]',
                'expiryInput' => 'input[name="' . EMERCHANTPAY_DIRECT_CODE . '_cc_expires"]'
            ),
            'hidden' => array(
                'expiryMonth' => EMERCHANTPAY_DIRECT_CODE . '_cc_expires_month',
                'expiryYear' => EMERCHANTPAY_DIRECT_CODE . '_cc_expires_year'
            )
        )
    );

    echo \EMerchantPay\Direct\TemplateManager::getCardStyleContent();
}
