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

use EMerchantPay\Checkout\Settings as EmpCheckoutSettings;
use EMerchantPay\Common            as EMerchantPayCommon;
use Genesis\Api\Constants\Transaction\Parameters\ScaExemptions;

class Installer extends \EMerchantPay\Base\Installer
{
    /**
     * Transaction DatabaseTableName
     * @var string
     */
    protected static $table_name = TABLE_EMERCHANTPAY_CHECKOUT_TRANSACTIONS;

    /**
     * Settings Values Prefix
     * @var string
     */
    protected static $settings_prefix = EMERCHANTPAY_CHECKOUT_SETTINGS_PREFIX;

    /**
     * Do on module install
     * @throws \Exception
     */
    public static function installModule()
    {
        global $messageStack;

        if (EmpCheckoutSettings::isInstalled()) {
            $messageStack->add_session('emerchantpay Checkout module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=' . EMERCHANTPAY_CHECKOUT_CODE, 'NONSSL'));
            return 'failed';
        }

        parent::installModule();

        static::createConsumersDbTable();
        static::addModuleConfigurationsToDb();
    }

    /**
     * Create database table for Genesis consumers
     *
     * @return void
     */
    protected static function createConsumersDbTable()
    {
        global $db;

        $db->Execute(
            'CREATE TABLE IF NOT EXISTS  `' .
                TABLE_EMERCHANTPAY_CHECKOUT_CONSUMERS . '` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `customer_id` int(10) unsigned NOT NULL,
                `customer_email` varchar(255) NOT NULL,
                `consumer_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `customer_email` (`customer_email`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8
              COMMENT=\'Tokenization consumers in Genesis\';'
        );
    }

    /**
     * Add modules settings to ZenCart configuration table
     *
     * @return void
     */
    protected static function addModuleConfigurationsToDb()
    {
        static::addMainConfigurationEntries();
        static::addCredentialsConfigurationEntries();
        static::addTransactionsConfigurationEntries();
        static::addWpfConfigurationEntries();
        static::addOrderConfigurationEntries();
        static::addThreedsOptions();
        static::addScaExemptionOptions();
    }

    /**
     * Helper function for options attributes in config
     *
     * @return string
     */
    private static function getRequiredOptionsAttributes()
    {
        return "array(''required'' => ''required'')";
    }

    /**
     * Inserts config entries for status, title
     *
     * @return void
     */
    private static function addMainConfigurationEntries()
    {
        global $db;

        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
             values
            ('Enable emerchantpay Checkout Module',
            '" . EmpCheckoutSettings::getCompleteSettingKey('STATUS') . "',
            'true',
            'Do you want to process payments via emerchantpay''s Genesis Gateway?',
            '6', '3', 'emp_zfg_draw_toggle(', 'emp_zfg_get_toggle_value', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Checkout Title',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'CHECKOUT_PAGE_TITLE'
            ) . "',
            'Pay safely with emerchantpay Checkout',
            'This name will be displayed on the checkout page', '6', '4',
            'emp_zfg_draw_input(null, ', now())"
        );
    }

    /**
     * Inserts config entries for username, password, environment
     *
     * @return void
     */
    private static function addCredentialsConfigurationEntries()
    {
        global $db;

        $requiredOptionsAttributes = static::getRequiredOptionsAttributes();

        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Genesis API Username',
            '" . EmpCheckoutSettings::getCompleteSettingKey('USERNAME') . "',
            '', 'Enter your Username, required for accessing the Genesis Gateway',
            '6', '4', 'emp_zfg_draw_input({$requiredOptionsAttributes}, ', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Genesis API Password',
            '" . EmpCheckoutSettings::getCompleteSettingKey('PASSWORD') . "',
            '', 'Enter your Password, required for accessing the Genesis Gateway',
            '6', '4', 'emp_zfg_draw_input({$requiredOptionsAttributes}, ', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Live Mode',
            '" . EmpCheckoutSettings::getCompleteSettingKey('ENVIRONMENT') . "',
            'false', 'If disabled, transactions are going through our Staging " .
            "(Test) server, NO MONEY ARE BEING TRANSFERRED', '6', '3',
            'emp_zfg_draw_toggle(', 'emp_zfg_get_toggle_value', now())"
        );
    }

    /**
     * Inserts config entries for transaction_types, allow_partial_capture,
     * allow_void, allow_partial_refund
     *
     * @return void
     */
    private static function addTransactionsConfigurationEntries()
    {
        global $db;

        $requiredOptionsAttributes = static::getRequiredOptionsAttributes();
        $transaction_types
            = EMerchantPayCommon::buildSettingsDropDownOptions(
                EmpCheckoutSettings::getTransactionsList()
            );

        $placeholder = ['none' => 'No specific Bank in use'];
        $availableBankCodes
            = $placeholder + EmpCheckoutSettings::getAvailableBankCodes();
        $bank_codes
            = EMerchantPayCommon::buildSettingsDropDownOptions(
                $availableBankCodes
            );

        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Transaction Types',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'TRANSACTION_TYPES'
            ) . "',
            '" . \Genesis\Api\Constants\Transaction\Types::SALE . "',
            'What transaction type should we use upon purchase?', '6', '0',
            'emp_zfg_select_drop_down_multiple({$requiredOptionsAttributes}, " .
            "{$transaction_types}, ', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Bank codes for Online banking',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'BANK_CODES'
            ) . "',
            'none',
            'Select Bank code(s) for Online banking transaction.', '6', '0',
            'emp_zfg_select_drop_down_multiple({$requiredOptionsAttributes}, " .
            "{$bank_codes}, ', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Partial Capture',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'ALLOW_PARTIAL_CAPTURE'
            ) . "',
            'true', 'Use this option to allow / deny Partial Capture Transactions',
            '6', '3', 'emp_zfg_draw_toggle(', 'emp_zfg_get_toggle_value', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Partial Refund',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'ALLOW_PARTIAL_REFUND'
            ) . "',
            'true', 'Use this option to allow / deny Partial Refund Transactions',
            '6', '3', 'emp_zfg_draw_toggle(', 'emp_zfg_get_toggle_value', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Cancel Transaction',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'ALLOW_VOID_TRANSACTIONS'
            ) . "',
            'true', 'Use this option to allow / deny Cancel Transactions', '6', '3',
            'emp_zfg_draw_toggle(', 'emp_zfg_get_toggle_value', now())"
        );
    }

    /**
     * Inserts config entries for language, wpf_tokenization, sort_order
     *
     * @return void
     */
    private static function addWpfConfigurationEntries()
    {
        global $db;

        $sortOrderAttributes = "array(''maxlength'' => ''3'')";
        $languages           = EMerchantPayCommon::buildSettingsDropDownOptions(
            EmpCheckoutSettings::getAvailableCheckoutLanguages()
        );

        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Checkout Page Language',
            '" . EmpCheckoutSettings::getCompleteSettingKey('LANGUAGE') . "',
            'en', 'What language (localization) should we have on the Checkout?.',
            '6', '0', 'emp_zfg_select_drop_down_single({$languages},', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('WPF Tokenization',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'WPF_TOKENIZATION'
            ) . "',
            'false', 'Enable WPF Tokenization', '6', '3', 'emp_zfg_draw_toggle(',
            'emp_zfg_get_toggle_value', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Sort order of display.',
            '" . EmpCheckoutSettings::getCompleteSettingKey('SORT_ORDER') . "',
            '0', 'Sort order of display. Lowest is displayed first.', '6', '0',
            'emp_zfg_draw_number_input({$sortOrderAttributes}, ', now())"
        );
    }

    /**
     * Inserts config entries for order_status_id, failed_order_status_id,
     * processed_order_status_id, refunded_order_status_id,
     * cancelled_order_status_id
     *
     * @return void
     */
    private static function addOrderConfigurationEntries()
    {
        global $db;

        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Set Default Order Status',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'ORDER_STATUS_ID'
            ) . "',
            '1', 'Set the default status of orders made with this payment module" .
            " to this value', '6', '0', 'emp_zfg_pull_down_order_statuses(',
            'zen_get_order_status_name', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Set Failed Order Status',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'FAILED_ORDER_STATUS_ID'
            ) . "',
            '1', 'Set the status of failed orders made with this payment module to" .
            " this value', '6', '0', 'emp_zfg_pull_down_order_statuses(',
            'zen_get_order_status_name', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Set Processed Order Status',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'PROCESSED_ORDER_STATUS_ID'
            ) . "',
            '2', 'Set the status of processed orders made with this payment " .
            "module to this value', '6', '0', 'emp_zfg_pull_down_order_statuses(',
            'zen_get_order_status_name', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Set Refunded Order Status',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'REFUNDED_ORDER_STATUS_ID'
            ) . "',
            '1', 'Set the status of refunded orders made with this payment module',
            '6', '0', 'emp_zfg_pull_down_order_statuses(',
            'zen_get_order_status_name', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Set Canceled Order Status',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'CANCELED_ORDER_STATUS_ID'
            ) . "',
            '1', 'Set the status of canceled orders made with this payment module',
            '6', '0', 'emp_zfg_pull_down_order_statuses(',
            'zen_get_order_status_name', now())"
        );
    }

    /**
     * Insert 3DSv2 options into DB
     *
     * @return void
     */
    private static function addThreedsOptions()
    {
        global $db;

        $challengeIndicators = EMerchantPayCommon::buildSettingsDropDownOptions(
            EmpCheckoutSettings::getChallengeIndicators()
        );

        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, use_function, date_added)
            values
            ('Enable 3DSv2',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'THREEDS_ALLOWED'
            ) . "',
            'true', 'Enable 3DSv2 optional parameters.',
             '6', '4', 'emp_zfg_draw_toggle(',
            'emp_zfg_get_toggle_value', now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('3DSv2 Challenge',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'THREEDS_CHALLENGE_INDICATOR'
            ) . "', 'no_preference', 
            'The value has weight and might impact the decision whether a 
            challenge will be required for the transaction or not.',
             '6', '4', 'emp_zfg_select_drop_down_single({$challengeIndicators},',
              now())"
        );
    }

    /**
     * Insert SCA excemption options into DB
     *
     * @return void
     */
    private static function addScaExemptionOptions()
    {
        global $db;

        $scaExemptionOptions = EMerchantPayCommon::buildSettingsDropDownOptions(
            [
                ScaExemptions::EXEMPTION_LOW_RISK  => 'Low risk',
                ScaExemptions::EXEMPTION_LOW_VALUE => 'Low value',
            ]
        );

        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('SCA Exemption',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'SCA_EXEMPTION'
            ) . "', '" . ScaExemptions::EXEMPTION_LOW_RISK . "', 
            'Exemption for the Strong Customer Authentication.',
             '6', '5', 'emp_zfg_select_drop_down_single({$scaExemptionOptions},',
              now())"
        );
        $db->Execute(
            'insert into ' . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value,
            configuration_description, configuration_group_id, sort_order,
            set_function, date_added)
            values
            ('Exemption Amount',
            '" . EmpCheckoutSettings::getCompleteSettingKey(
                'SCA_EXEMPTION_AMOUNT'
            ) . "',
            '100', 'Exemption Amount determinate if the SCA Exemption should
             be included in the request to the Gateway.',
            '6', '6', 'emp_zfg_draw_input(null, ', now())"
        );
    }

    /**
     * Do on module remove
     *
     * @throws \Exception
     * @return void
     */
    public static function removeModule()
    {
        global $db;

        parent::removeModule();

        $db->Execute(
            'DROP TABLE IF EXISTS `' . TABLE_EMERCHANTPAY_CHECKOUT_CONSUMERS . '`'
        );
    }
}
