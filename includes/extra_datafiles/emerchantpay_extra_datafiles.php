<?php
/*
 * Copyright (C) 2018 emerchantpay Ltd.
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
 * @author      emerchantpay
 * @copyright   2018 emerchantpay Ltd.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

define('EMERCHANTPAY_CHECKOUT_CODE', 'emerchantpay_checkout');
define('FILENAME_EMECHANTPAY_CHECKOUT_IPN', 'emerchantpay_checkout_ipn');
define('EMERCHANTPAY_CHECKOUT_SETTINGS_PREFIX', 'MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_');
define('TABLE_EMERCHANTPAY_CHECKOUT_TRANSACTIONS', DB_PREFIX . 'emerchantpay_checkout_transactions');
define('TABLE_EMERCHANTPAY_CHECKOUT_CONSUMERS', DB_PREFIX . 'emerchantpay_checkout_consumers');

define('EMERCHANTPAY_DIRECT_CODE', 'emerchantpay_direct');
define('FILENAME_EMECHANTPAY_DIRECT_IPN', 'emerchantpay_direct_ipn');
define('EMERCHANTPAY_DIRECT_SETTINGS_PREFIX', 'MODULE_PAYMENT_EMERCHANTPAY_DIRECT_');
define('TABLE_EMERCHANTPAY_DIRECT_TRANSACTIONS', DB_PREFIX . 'emerchantpay_direct_transactions');
define('PPRO_TRANSACTION_SUFFIX', '_ppro');
define('GOOGLE_PAY_TRANSACTION_PREFIX', 'google_pay_');
define('GOOGLE_PAY_PAYMENT_TYPE_AUTHORIZE', 'authorize');
define('GOOGLE_PAY_PAYMENT_TYPE_SALE', 'sale');
define('PAYPAL_TRANSACTION_PREFIX', 'pay_pal_');
define('PAYPAL_PAYMENT_TYPE_AUTHORIZE', 'authorize');
define('PAYPAL_PAYMENT_TYPE_SALE', 'sale');
define('PAYPAL_PAYMENT_TYPE_EXPRESS', 'express');
define('APPLE_PAY_TRANSACTION_PREFIX', 'apple_pay_');
define('APPLE_PAY_PAYMENT_TYPE_AUTHORIZE', 'authorize');
define('APPLE_PAY_PAYMENT_TYPE_SALE', 'sale');
define('METHOD_ACTION_CAPTURE', 'capture');
define('METHOD_ACTION_REFUND', 'refund');
