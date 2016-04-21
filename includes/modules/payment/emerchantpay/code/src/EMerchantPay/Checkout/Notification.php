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

namespace EMerchantPay\Checkout;

use \EMerchantPay\Checkout\Settings as EMerchantPayCheckoutSettings;
use \EMerchantPay\Common as EMerchantPayCommon;
use \EMerchantPay\Checkout\Transaction as EMerchantPayCheckoutTransaction;
use \EMerchantPay\Checkout\TransactionProcess as EMerchantPayCheckoutTransactionProcess;

class Notification extends \EMerchantPay\Base\Notification
{
    /**
     * ModuleCode, used for redirections and loading files
     * @var string
     */
    protected static $module_code = EMERCHANTPAY_CHECKOUT_CODE;

    /**
     * Process Genesis Notification
     * @param array $requestData
     */
    protected static function processNotification($requestData)
    {
        global $db;

        if (!EMerchantPayCheckoutSettings::getStatus()) {
            exit(0);
        }

        parent::processNotification($requestData);
        EMerchantPayCheckoutTransactionProcess::bootstrap();

        try {
            $notification = new \Genesis\API\Notification($requestData);

            if ($notification->isAuthentic()) {
                $notification->initReconciliation();

                $reconcile = $notification->getReconciliationObject();
                $timestamp = EMerchantPayCommon::formatTimeStamp($reconcile->timestamp);

                $data = array(
                    'unique_id' => $reconcile->unique_id,
                    'status'    => $reconcile->status,
                    'currency'  => $reconcile->currency,
                    'amount'    => $reconcile->amount,
                    'timestamp' => $timestamp,
                );

                EMerchantPayCheckoutTransaction::populateTransaction($data);


                if (isset($reconcile->payment_transaction)) {
                    $payment = $reconcile->payment_transaction;

                    $timestamp = EMerchantPayCommon::formatTimeStamp($payment->timestamp);

                    $order_id = EMerchantPayCheckoutTransaction:: getOrderByTransaction($reconcile->unique_id);

                    $data = array(
                        'order_id'          => $order_id,
                        'reference_id'      => $reconcile->unique_id,
                        'unique_id'         => $payment->unique_id,
                        'type'              => $payment->transaction_type,
                        'mode'              => $payment->mode,
                        'status'            => $payment->status,
                        'currency'          => $payment->currency,
                        'amount'            => $payment->amount,
                        'timestamp'         => $timestamp,
                        'terminal_token'    => isset($payment->terminal_token) ? $payment->terminal_token : '',
                        'message'           => isset($payment->message) ? $payment->message : '',
                        'technical_message' => isset($payment->technical_message) ? $payment->technical_message : '',
                    );

                    EMerchantPayCheckoutTransaction::populateTransaction($data);

                    $orderQuery = $db->Execute("SELECT
                                                  `orders_id`, `orders_status`, `currency`, `currency_value`
                                                FROM " . TABLE_ORDERS . "
                                                WHERE `orders_id` = '" . abs(intval($order_id)) . "'");

                    if ($orderQuery->RecordCount() < 1) {
                        exit(0);
                    }

                    $order = $orderQuery->fields;

                    switch ($payment->status) {
                        case \Genesis\API\Constants\Transaction\States::APPROVED:
                            $order_status_id = EMerchantPayCheckoutSettings::getProcessedOrderStatusID();
                            break;
                        case \Genesis\API\Constants\Transaction\States::ERROR:
                        case \Genesis\API\Constants\Transaction\States::DECLINED:
                            $order_status_id = EMerchantPayCheckoutSettings::getFailedOrderStatusID();
                            break;
                        default:
                            $order_status_id = EMerchantPayCheckoutSettings::getOrderStatusID();
                    }

                    EMerchantPayCheckoutTransaction::setOrderStatus(
                        $order['orders_id'],
                        $order_status_id
                    );

                    EMerchantPayCheckoutTransaction::performOrderStatusHistory(
                        array(
                            'type'            => 'Notification',
                            'orders_id'       => $order['orders_id'],
                            'order_status_id' => $order_status_id,
                            'payment'         => array(
                                'unique_id' => $payment->unique_id,
                                'status'    => $payment->status,
                                'message'   => $payment->message
                            )
                        )
                    );
                } else {
                    $order_id = EMerchantPayCheckoutTransaction::getOrderByTransaction($reconcile->unique_id);

                    $orderQuery = $db->Execute("SELECT
                                                  `orders_id`, `orders_status`, `currency`, `currency_value`
                                                FROM " . TABLE_ORDERS . "
                                                WHERE `orders_id` = '" . abs(intval($order_id)) . "'");

                    if ($orderQuery->RecordCount() < 1) {
                        exit(0);
                    }

                    $order = $orderQuery->fields;

                    $order_status_id = EMerchantPayCheckoutSettings::getFailedOrderStatusID();

                    EMerchantPayCheckoutTransaction::setOrderStatus(
                        $order['orders_id'],
                        $order_status_id
                    );

                    EMerchantPayCheckoutTransaction::performOrderStatusHistory(
                        array(
                            'type'            => 'Notification',
                            'orders_id'       => $order['orders_id'],
                            'order_status_id' => $order_status_id,
                            'payment'         => array(
                                'unique_id' => $reconcile->unique_id,
                                'status'    => $reconcile->status,
                                'message'   => $reconcile->message
                            )
                        )
                    );
                }

                $notification->renderResponse();
            }
        } catch (\Exception $e) {
            //hide all exceptions
        }

        exit(0);
    }

    /**
     * Build Return URL from Genesis
     * @param string $action
     * @return string
     */
    public static function buildReturnURL($action)
    {
        return html_entity_decode(
            zen_href_link(
                FILENAME_EMECHANTPAY_CHECKOUT_IPN,
                "return=$action",
                "SSL",
                false
            )
        );
    }
}
