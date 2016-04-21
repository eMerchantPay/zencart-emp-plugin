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

namespace EMerchantPay\Base;

abstract class Notification
{
    /**
     * ModuleCode, used for redirections and loading files
     * @var string
     */
    protected static $module_code = null;

    /**
     * Build Return URL from Genesis
     * @param string $action
     * @return string
     */
    public static function buildReturnURL($action)
    {
        return null;
    }

    /**
     * Resets ZenCart Checkout Sessions (On Successful Payment)
     */
    public static function resetCartSessions()
    {
        $_SESSION['cart']->reset(true);
        unset($_SESSION['sendto']);
        unset($_SESSION['billto']);
        unset($_SESSION['shipping']);
        unset($_SESSION['payment']);
        unset($_SESSION['comments']);
    }

    /**
     * Process Genesis Notification
     * @param array $requestData
     */
    protected static function processNotification($requestData)
    {
    }

    /**
     * Build NotificationURL for Genesis
     * @return string
     */
    public static function buildNotificationUrl()
    {
        return static::buildReturnURL('notify');
    }

    /**
     * Handle Genesis Notification / Redirects
     * @param array $requestData
     */
    public static function handleNotification($requestData)
    {
        global $messageStack;

        if (!isset($_GET['return'])) {
            return;
        }

        if (!defined('DIR_FS_CATALOG_LANGUAGES')) {
            define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG . 'includes/languages/');
        }

        $moduleLanguageFile =
            DIR_FS_CATALOG_LANGUAGES .
            $_SESSION['language'] .
            '/modules/payment/' .
            static::$module_code . '.php';

        if (file_exists($moduleLanguageFile)) {
            require_once($moduleLanguageFile);
        }

        $action = $_GET['return'];

        switch ($action) {
            case 'notify':
                static::processNotification($requestData);
                break;

            case 'success':
                static::resetCartSessions();
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
                break;

            case 'failure':
                $messageStack->add_session(
                    'checkout_payment',
                    constant("MODULE_PAYMENT_" . strtoupper(static::$module_code) . "_MESSAGE_PAYMENT_FAILED"),
                    'error'
                );
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
                break;

            case 'cancel':
                $messageStack->add_session(
                    'checkout_payment',
                    constant("MODULE_PAYMENT_" . strtoupper(static::$module_code) . "_MESSAGE_PAYMENT_CANCELED"),
                    'caution'
                );
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
                break;
        }
    }
}
