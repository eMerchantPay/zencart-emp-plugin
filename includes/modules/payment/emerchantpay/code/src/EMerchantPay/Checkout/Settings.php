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
use Genesis\Api\Constants\Banks;
use Genesis\Api\Constants\Payment\Methods;
use Genesis\Api\Constants\Transaction\Names;
use Genesis\Api\Constants\Transaction\Parameters\Mobile\ApplePay\PaymentTypes as ApplePaymentTypes;
use Genesis\Api\Constants\Transaction\Parameters\Mobile\GooglePay\PaymentTypes as GooglePaymentTypes;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\Control\ChallengeIndicators;
use Genesis\Api\Constants\Transaction\Parameters\Wallets\PayPal\PaymentTypes as PayPalPaymentTypes;
use Genesis\Api\Constants\Transaction\Types;

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
    protected static $prefix = EMERCHANTPAY_CHECKOUT_SETTINGS_PREFIX;

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

        // Exclude PPRO transaction. This is not standalone transaction type
        array_push($excludedTypes, Types::PPRO);

        // Exclude Google Pay transaction. This will serve Google Pay payment methods
        array_push($excludedTypes, Types::GOOGLE_PAY);

        // Exclude PayPal transaction. This will serve PayPal payment methods
        array_push($excludedTypes, Types::PAY_PAL);

        // Exclude Apple Pay transaction
        array_push($excludedTypes, Types::APPLE_PAY);

        // Exclude Transaction Types
        $transactionTypes = array_diff($transactionTypes, $excludedTypes);

        // Add Google Pay types
        $googlePayTypes = array_map(
            function ($type) {
                return GOOGLE_PAY_TRANSACTION_PREFIX . $type;
            },
            [
                GooglePaymentTypes::AUTHORIZE,
                GooglePaymentTypes::SALE
            ]
        );

        // Add PayPal types
        $payPalTypes = array_map(
            function ($type) {
                return PAYPAL_TRANSACTION_PREFIX . $type;
            },
            [
                PayPalPaymentTypes::AUTHORIZE,
                PayPalPaymentTypes::SALE,
                PayPalPaymentTypes::EXPRESS,
            ]
        );

        // Add Apple Pay types
        $applePayTypes = array_map(
            function ($type) {
                return APPLE_PAY_TRANSACTION_PREFIX . $type;
            },
            [
                ApplePaymentTypes::AUTHORIZE,
                ApplePaymentTypes::SALE
            ]
        );

        $transactionTypes = array_merge(
            $transactionTypes,
            $googlePayTypes,
            $payPalTypes,
            $applePayTypes
        );
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
        $isoCodes = \Genesis\Api\Constants\i18n::getAll();

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
        static::appendSettingKey(
            $keys,
            'TRANSACTION_TYPES',
            'BANK_CODES'
        );
        $keys[] = static::getPrefix() . 'LANGUAGE';
        $keys[] = static::getPrefix() . 'WPF_TOKENIZATION';
        $keys[] = static::getPrefix() . 'THREEDS_ALLOWED';
        $keys[] = static::getPrefix() . 'THREEDS_CHALLENGE_INDICATOR';
        $keys[] = static::getPrefix() . 'SCA_EXEMPTION';
        $keys[] = static::getPrefix() . 'SCA_EXEMPTION_AMOUNT';

        return $keys;
    }

    /**
     * Get Selected Transaction Types
     */
    public static function getTransactionTypes()
    {
        $transaction_types = static::getSetting("TRANSACTION_TYPES");

        // Trim selected values for payment types and reorder them
        return static::orderCardTransactionTypes(
            array_map(
                'trim',
                explode(
                    ',',
                    $transaction_types
                )
            )
        );
    }

    /**
     * Get Selected Payment methods for Online banking
     *
     * @return array
     */
    public static function getSelectedBankCodes()
    {
        $bankCodes = static::getSetting("BANK_CODES");

        return
            array_map(
                'trim',
                explode(
                    ',',
                    $bankCodes
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

    /**
     * List of all available Payment method for Online banking
     *
     * @return array
     */
    public static function getAvailableBankCodes()
    {
        return [
            Banks::CPI => 'Interac Combined Pay-in',
            Banks::BCT => 'Bancontact',
            Banks::BLK => 'BLIK',
            Banks::SE  => 'SPEI',
            Banks::PID => 'LatiPay'
        ];
    }

    /**
     * List of available challenge indicators
     *
     * @return string[]
     */
    public static function getChallengeIndicators()
    {
        return [
            ChallengeIndicators::NO_PREFERENCE          => 'No preference',
            ChallengeIndicators::NO_CHALLENGE_REQUESTED => 'No challenge requested',
            ChallengeIndicators::PREFERENCE             => 'Preference',
            ChallengeIndicators::MANDATE                => 'Mandate'
        ];
    }

    /**
     * Get SCA Exemption value
     *
     * @return string
     */
    public static function getScaExemption()
    {
        return static::getSetting('SCA_EXEMPTION');
    }

    /**
     * Get SCA Exemption amount
     *
     * @return float
     */
    public static function getScaExemptionAmount()
    {
        return max((float)static::getSetting('SCA_EXEMPTION_AMOUNT'), 0);
    }

    /**
     * Order transaction types with Card Transaction types in front
     *
     * @param array $selected_types Selected transaction types
     *
     * @return array
     */
    private static function orderCardTransactionTypes($selected_types)
    {
        $order = \Genesis\Api\Constants\Transaction\Types::getCardTransactionTypes();

        asort($selected_types);

        $sorted_array = array_intersect($order, $selected_types);

        return array_merge(
            $sorted_array,
            array_diff($selected_types, $sorted_array)
        );
    }
}
