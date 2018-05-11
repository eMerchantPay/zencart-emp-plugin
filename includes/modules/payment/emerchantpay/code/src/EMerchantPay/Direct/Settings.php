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

namespace EMerchantPay\Direct;

use \EMerchantPay\Common as EMerchantPayCommon;

class Settings extends \EMerchantPay\Base\Settings
{
    /**
     * Settings Values Prefix
     * @var string
     */
    static protected $prefix = EMERCHANTPAY_DIRECT_SETTINGS_PREFIX;

    /**
     * Default value Genesis Login
     * @var string
     */
    protected static $placeholderToken = 'Enter your Genesis Token here';

    /**
     * Get if it is allowed to display the module on the checkout page
     * @return bool
     */
    public static function getIsAvailableOnCheckoutPage()
    {
        return
            parent::getIsAvailableOnCheckoutPage() &&
            EMerchantPayCommon::getIsSSLEnabled();
    }

    /**
     * Get if module required settings are set properly
     * @return bool
     */
    public static function getIsConfigured()
    {
        return
            parent::getIsConfigured() &&
            !empty(static::getToken());
    }

    /**
     * Get Default value for Genesis Token
     * @return string
     */
    public static function getPlaceHolderToken()
    {
        return static::$placeholderToken;
    }

    /**
     * Gets a list of the available transaction types for a payment method
     * @return array
     */
    public static function getTransactionsList()
    {
        return array(
            \Genesis\API\Constants\Transaction\Types::AUTHORIZE    => "Authorize",
            \Genesis\API\Constants\Transaction\Types::AUTHORIZE_3D => "Authorize 3D",
            \Genesis\API\Constants\Transaction\Types::SALE         => "Sale",
            \Genesis\API\Constants\Transaction\Types::SALE_3D      => "Sale 3D"
        );
    }

    /**
     * Get available payment templates for the checkout page
     * @return array
     */
    public static function getPaymentTemplatesList()
    {
        return array(
            'integrated'         => 'Integrated',
            'zencart-default'    => 'Zencart-Default'
        );
    }

    /**
     * Get available settings to manage
     * @return array
     */
    public static function getSettingKeys()
    {
        $keys = parent::getSettingKeys();

        static::appendSettingKey($keys, "PASSWORD", "TOKEN");
        static::appendSettingKey($keys, "ENVIRONMENT", "TRANSACTION_TYPE");
        static::appendSettingKey($keys, "SORT_ORDER", "PAYMENT_TEMPLATE", 'before');

        return $keys;
    }

    /**
     * Get Genesis Token Setting Value
     * @return string
     */
    public static function getToken()
    {
        return static::getSetting("TOKEN");
    }

    /**
     * Get Selected Processing API Transaction Type
     * @return string
     */
    public static function getTransactionType()
    {
        return static::getSetting("TRANSACTION_TYPE");
    }

    /**
     * Get Payment Template Setting Value
     * @return string
     */
    public static function getPaymentTemplate()
    {
        return static::getSetting("PAYMENT_TEMPLATE");
    }

    /**
     * Get Payment Template Setting Value
     * @return string
     */
    public static function getShouldUseIntegratedPaymentTemplate()
    {
        return static::getPaymentTemplate() == 'integrated';
    }
}
