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

namespace EMerchantPay\Checkout;

use EMerchantPay\Helpers\TransactionsHelper;
use Genesis\API\Constants\Payment\Methods;
use Genesis\API\Constants\Transaction\Names;
use Genesis\API\Constants\Transaction\Types;

/**
 * Class Settings
 *
 * @category EMerchantPay
 *
 * @package EMerchantPay\Checkout
 * @author  Client Inegrations <client_integrations@emerchantpay.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU, version 2 (GPL-2.0)
 * @link    https://emerchantpay.com
 */
class Settings extends \EMerchantPay\Base\Settings
{
    /**
     * Settings Values Prefix
     *
     * @var string
     */
    static protected $prefix = EMERCHANTPAY_CHECKOUT_SETTINGS_PREFIX;

    /**
     * Gets a list of the available transaction types for a payment method
     *
     * @return array
     */
    public static function getTransactionsList()
    {
        $data = array();

        $transactionTypes = Types::getWPFTransactionTypes();
        $excludedTypes    = TransactionsHelper::getRecurringTransactionTypes();

        // Exclude Transaction Types
        $transactionTypes = array_diff($transactionTypes, $excludedTypes);

        // Add PPRO types
        $pproTypes = array_map(
            function ($type) {
                return $type . PPRO_TRANSACTION_SUFFIX;
            },
            Methods::getMethods()
        );

        $transactionTypes = array_merge($transactionTypes, $pproTypes);
        asort($transactionTypes);

        foreach ($transactionTypes as $type) {
            $name = Names::getName($type);
            if (!Types::isValidTransactionType($type)) {
                $name = strtoupper($type);
            }

            $data[$type] = $name;
        }

        return $data;
    }

    /**
     * Get available WPF languages
     *
     * @return array
     */
    public static function getAvailableCheckoutLanguages()
    {
        $data     = array();
        $isoCodes = \Genesis\API\Constants\i18n::getAll();

        foreach ($isoCodes as $isoCode) {
            $data[$isoCode] = TransactionsHelper::getLanguageByIsoCode($isoCode);
        }

        return $data;
    }

    /**
     * Get available settings to manage
     * @return array
     */
    public static function getSettingKeys()
    {
        $keys = parent::getSettingKeys();

        static::appendSettingKey($keys, 'ENVIRONMENT', 'TRANSACTION_TYPES');
        $keys[] = static::getPrefix() . 'LANGUAGE';
        $keys[] = static::getPrefix() . 'WPF_TOKENIZATION';

        return $keys;
    }

    /**
     * Get Selected Transaction Types
     */
    public static function getTransactionTypes()
    {
        $transaction_types = static::getSetting("TRANSACTION_TYPES");
        return
            array_map(
                'trim',
                explode(
                    ',',
                    $transaction_types
                )
            );
    }

    /**
     * Get Checkout Language for the Genesis WPF
     * @param string $default
     * @return string
     */
    public static function getLanguage($default = 'en')
    {
        return (static::getSetting("LANGUAGE") ?: $default);
    }
}
