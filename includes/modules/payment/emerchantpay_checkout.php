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

require DIR_FS_CATALOG . DIR_WS_INCLUDES . "modules/payment/emerchantpay/code/vendor/autoload.php";

use EMerchantPay\Checkout\Installer          as EMerchantPayCheckoutInstaller;
use EMerchantPay\Checkout\Settings           as EMerchantPayCheckoutSettings;
use EMerchantPay\Checkout\Transaction        as EMerchantPayCheckoutTransaction;
use EMerchantPay\Checkout\TransactionProcess as EMerchantPayCheckoutTransactionProcess;
use EMerchantPay\Checkout\Notification       as EMerchantPayCheckoutNotification;

class emerchantpay_checkout extends \EMerchantPay\Base\PaymentMethod // phpcs:ignore
{
    /**
     * Check if the module is installed
     *
     * @var bool
     */
    protected $check;

    /**
     * Generate Reference Transaction (Capture, Refund, Void)
     * @param string $transaction_type
     * @param stdClass $data
     * @return stdClass
     */
    protected function getReferenceTransactionResponse($transaction_type, $data)
    {
        return EMerchantPayCheckoutTransactionProcess::$transaction_type($data);
    }

    /**
     * Extends the parameters needed for displaying the admin-page components
     * @param array $data
     */
    protected function extendOrderTransPanelData(&$data)
    {
        $data->params['modal'] = array(
            'capture' => array(
                'allowed' => EMerchantPayCheckoutSettings::isPartialCaptureAllowed(),
                'form' => array(
                    'action' => 'doCapture',
                ),
                'input' => array(
                    'visible' => true,
                )
            ),
            'refund' => array(
                'allowed' => EMerchantPayCheckoutSettings::isPartialRefundAllowed(),
                'form' => array(
                    'action' => 'doRefund',
                ),
                'input' => array(
                    'visible' => true,
                )
            ),
            'void' => array(
                'allowed' => EMerchantPayCheckoutSettings::isVoidTransactionAllowed(),
                'form' => array(
                    'action' => 'doVoid',
                ),
                'input' => array(
                    'visible' => false,
                )
            )
        );

        $data->translations = array(
            'panel' => array(
                'title' =>
                    MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_TITLE,
                'transactions' => array(
                    'header' => array(
                        'id' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_ID,
                        'type' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_TYPE,
                        'timestamp' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_TIMESTAMP,
                        'amount' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_AMOUNT,
                        'status' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_STATUS,
                        'message' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_MESSAGE,
                        'mode' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_MODE,
                        'action_capture' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_ACTION_CAPTURE,
                        'action_refund' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_ACTION_REFUND,
                        'action_void' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_HEADER_ACTION_VOID
                    )
                )
            ),
            'modal' => array(
                'capture' => array(
                    'title' =>
                        MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_CAPTURE_TRAN_TITLE,
                    'input' => array(
                        'label' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_MODAL_AMOUNT_LABEL_CAPTURE,
                        'warning_tooltip' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_MESSAGE_CAPTURE_PARTIAL_DENIED
                    ),
                    'buttons' => array(
                        'submit' => array(
                            'title' =>
                                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_BUTTON_CAPTURE
                        ),
                        'cancel' => array(
                            'title' =>
                                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_BUTTON_CANCEL
                        )
                    )
                ),
                'refund' => array(
                    'title' =>
                        MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_REFUND_TRAN_TITLE,
                    'input' => array(
                        'label' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_ORDER_TRANS_MODAL_AMOUNT_LABEL_REFUND,
                        'warning_tooltip' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_MESSAGE_REFUND_PARTIAL_DENIED
                    ),
                    'buttons' => array(
                        'submit' => array(
                            'title' =>
                                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_BUTTON_REFUND
                        ),
                        'cancel' => array(
                            'title' =>
                                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_BUTTON_CANCEL
                        )
                    )
                ),
                'void' => array(
                    'title' =>
                        MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_VOID_TRAN_TITLE,
                    'input' => array(
                        'label' => null,
                        'warning_tooltip' =>
                            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_MESSAGE_VOID_DENIED
                    ),
                    'buttons' => array(
                        'submit' => array(
                            'title' =>
                                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_BUTTON_VOID
                        ),
                        'cancel' => array(
                            'title' =>
                                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_LABEL_BUTTON_CANCEL
                        )
                    )
                )
            )
        );
    }

    /**
     * Store data to an existing / a new Transaction
     * @array $data
     * @return mixed
     */
    protected function doPopulateTransaction($data)
    {
        EMerchantPayCheckoutTransaction::populateTransaction($data);
    }

    /**
     * Save Order Status History to the database (after Capture, Refund Void)
     * @param array $data
     * @return mixed
     */
    protected function doPerformOrderStatusHistory($data)
    {
        switch ($data['transaction_type']) {
            case \Genesis\Api\Constants\Transaction\Types::CAPTURE:
                $data['type'] = 'Captured';
                $data['order_status_id'] = EMerchantPayCheckoutSettings::getProcessedOrderStatusID();
                break;

            case \Genesis\Api\Constants\Transaction\Types::REFUND:
                $data['type'] = 'Refunded';
                $data['order_status_id'] = EMerchantPayCheckoutSettings::getRefundedOrderStatusID();
                break;

            case \Genesis\Api\Constants\Transaction\Types::VOID:
                $data['type'] = 'Voided';
                $data['order_status_id'] = EMerchantPayCheckoutSettings::getCanceledOrderStatusID();
                break;
        }

        if (isset($data['type']) && isset($data['order_status_id'])) {
            EMerchantPayCheckoutTransaction::setOrderStatus(
                $data['orders_id'],
                $data['order_status_id']
            );
            EMerchantPayCheckoutTransaction::performOrderStatusHistory($data);
        }
    }

    /**
     * Used to determine the Module Transactions Table Name
     * @return string
     */
    protected function getTableNameTransactions()
    {
        return TABLE_EMERCHANTPAY_CHECKOUT_TRANSACTIONS;
    }

    /**
     * Get the sum of the ammount for a list of transaction types and status
     * @param int $order_id
     * @param string $reference_id
     * @param array $types
     * @param string $status
     * @return float
     */
    protected function getTransactionsSumAmount($order_id, $reference_id, $types, $status)
    {
        return EMerchantPayCheckoutTransaction::getTransactionsSumAmount(
            $order_id,
            $reference_id,
            $types,
            $status
        );
    }

    /**
     * Get saved transaction by id
     *
     * @param string $reference_id UniqueId of the transaction
     *
     * @return mixed bool on fail, row on success
     */
    protected function getTransactionById($unique_id)
    {
        return EMerchantPayCheckoutTransaction::getTransactionById($unique_id);
    }

    /**
     * Get the detailed transactions list of an order for transaction types and status
     * @param int $order_id
     * @param string $reference_id
     * @param array $transaction_types
     * @param string $status
     * @return array
     */
    protected function getTransactionsByTypeAndStatus($order_id, $reference_id, $transaction_types, $status)
    {
        return EMerchantPayCheckoutTransaction::getTransactionsByTypeAndStatus(
            $order_id,
            $reference_id,
            $transaction_types,
            $status
        );
    }

    /**
     * Check to see whether module is installed
     *
     * @return boolean
     */
    public function check()
    {
        global $db;
        if (!isset($this->check)) {
            $check_query =
                $db->Execute(
                    "select configuration_value from " . TABLE_CONFIGURATION . "
                     where configuration_key = '" .
                        EMerchantPayCheckoutSettings::getCompleteSettingKey("STATUS") . "'"
                );
            $this->check = $check_query->RecordCount();
        }
        return $this->check;
    }

    /**
     * Registers Genesis autoload for a specific payment module.
     */
    protected function registerLibraries()
    {
        EMerchantPayCheckoutTransactionProcess::bootstrap();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->code = EMERCHANTPAY_CHECKOUT_CODE;
        $this->version = "1.2.6";
        parent::__construct();
    }

    protected function init()
    {
        $this->enabled = EMerchantPayCheckoutSettings::isEnabled();
        if (IS_ADMIN_FLAG === true) {
            // Payment module title in Admin
            $this->title = MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_TEXT_TITLE;

            if (EMerchantPayCheckoutSettings::isInstalled()) {
                if (!EMerchantPayCheckoutSettings::isConfigured()) {
                    $this->title .= '<span class="alert"> (Not Configured)</span>';
                } elseif (!EMerchantPayCheckoutSettings::isEnabled()) {
                    $this->title .= '<span class="alert"> (Disabled)</span>';
                } elseif (!EMerchantPayCheckoutSettings::isLiveMode()) {
                    $this->title .= '<span class="alert-warning"> (Staging Mode)</span>';
                } else {
                    $this->title .= '<span class="alert-success"> (Live Mode)</span>';
                }
            }
        } else {
            // Payment module title in Catalog
            $this->title = MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_TEXT_PUBLIC_TITLE;
        }
        // Descriptive Info about module in Admin
        $this->description =
            sprintf(
                "<div style=\"text-align: center;\"><strong>%s</strong><br />(rev. %s)</div>",
                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_TEXT_TITLE,
                $this->version
            ) .
            MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_TEXT_DESCRIPTION;
        // Sort Order of this payment option on the customer payment page
        $this->sort_order = EMerchantPayCheckoutSettings::getSortOrder();
        $this->order_status = (int)DEFAULT_ORDERS_STATUS_ID;
        if (EMerchantPayCheckoutSettings::getOrderStatusID() > 0) {
            $this->order_status = EMerchantPayCheckoutSettings::getOrderStatusID();
        }

        parent::init();
    }

    /**
     * calculate zone matches and flag settings to determine whether this module should display to customers or not
     *
     */
    public function update_status() // phpcs:ignore
    {
        $this->enabled = EMerchantPayCheckoutSettings::getIsAvailableOnCheckoutPage();
    }

    /**
     * Display Information Submission Fields on the Checkout Payment Page
     *
     * @return array
     */
    public function selection()
    {
        $selection = array(
            'id' => $this->code,
            'module' =>
                EMerchantPayCheckoutSettings::getCheckoutPageTitle(
                    MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_TEXT_TITLE
                ) .
                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_TEXT_PUBLIC_CHECKOUT_CONTAINER
        );

        return $selection;
    }

    /**
     * Process a checkout request
     *
     * This method will try to create a new WPF instance
     * if successful - we redirect the customer on "after_process" to the newly created instance
     * if unsuccessful - we show them an error message and redirecting back to the CHECKOUT PAYMENT PAGE
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    public function before_process() // phpcs:ignore
    {
        global $order, $messageStack;

        $prefix = self::PLATFORM_TRANSACTION_PREFIX;
        $data = new stdClass();
        $data->transaction_id = self::generateTransactionId($prefix);
        $data->description = '';

        foreach ($order->products as $product) {
            $separator = ($product == end($order->products)) ? '' : PHP_EOL;

            $data->description .= $product['qty'] . ' x ' . $product['name'] . $separator;
        }

        $data->currency = $order->info['currency'];

        $data->language_id = EMerchantPayCheckoutSettings::getLanguage();

        $data->urls = array(
            'notification'   =>
                EMerchantPayCheckoutNotification::buildNotificationUrl(),
            'return_success' =>
                EMerchantPayCheckoutNotification::buildReturnURL(
                    EMerchantPayCheckoutNotification::ACTION_SUCCESS
                ),
            'return_failure' =>
                EMerchantPayCheckoutNotification::buildReturnURL(
                    EMerchantPayCheckoutNotification::ACTION_FAILURE
                ),
            'return_cancel' =>
                EMerchantPayCheckoutNotification::buildReturnURL(
                    EMerchantPayCheckoutNotification::ACTION_CANCEL
                )
        );

        $data->order = $order;

        $errorMessage = null;

        try {
            $response = EMerchantPayCheckoutTransactionProcess::pay($data);

            $this->responseObject = $response->getResponseObject();

            if (isset($this->responseObject->consumer_id)) {
                EMerchantPayCheckoutTransactionProcess::saveConsumerId(
                    $this->responseObject->consumer_id
                );
            }

            if ($response->isSuccessful()) {
                return true;
            }

            $errorMessage = !empty($this->responseObject->message) ? $this->responseObject->message :
                MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_MESSAGE_PAYMENT_FAILED;
            $this->responseObject = null;
        } catch (\Genesis\Exceptions\ErrorNetwork $e) {
            $errorMessage = MODULE_PAYMENT_EMERCHANTPAY_CHECKOUT_MESSAGE_CHECK_CREDENTIALS .
                            PHP_EOL .
                            $e->getMessage();
            $this->responseObject = null;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->responseObject = null;
        }
        if (empty($this->responseObject) && !empty($errorMessage)) {
            $messageStack->add_session('checkout_payment', $errorMessage, 'error');
            zen_redirect(
                zen_href_link(
                    FILENAME_CHECKOUT_PAYMENT,
                    'payment_error=' . get_class($this),
                    'SSL'
                )
            );
        }
    }

    /**
     * Build admin-page components
     *
     * @param int $zf_order_id
     * @return string
     */
    public function admin_notification($zf_order_id) // phpcs:ignore
    {
        if (EMerchantPayCheckoutSettings::isInstalled()) {
            return parent::admin_notification($zf_order_id);
        } else {
            return false;
        }
    }

    /**
     * Install the payment module and its configuration settings
     *
     */
    public function install()
    {
        EMerchantPayCheckoutInstaller::installModule();
    }

    /**
     * Remove the module and all its settings
     *
     */
    public function remove()
    {
        EMerchantPayCheckoutInstaller::removeModule();
    }
    /**
     * Internal list of configuration keys used for configuration of the module
     *
     * @return array
     */
    public function keys()
    {
        return EMerchantPayCheckoutSettings::getSettingKeys();
    }
}
