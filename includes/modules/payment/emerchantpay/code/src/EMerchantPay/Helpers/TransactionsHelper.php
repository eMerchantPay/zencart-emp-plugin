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

namespace EMerchantPay\Helpers;

use Genesis\Api\Constants\Financial\Alternative\Transaction\ItemTypes;
use Genesis\Api\Constants\Transaction\Types;
use Genesis\Api\Request\Financial\Alternatives\Transaction\Item;
use Genesis\Api\Request\Financial\Alternatives\Transaction\Items;
use Genesis\Exceptions\ErrorParameter;
use Genesis\Exceptions\InvalidArgument;

/**
 * Class TransactionsHelper
 *
 * @category EMerchantPay
 *
 * @package EMerchantPay\Helpers
 * @author  Client Inegrations <client_integrations@emerchantpay.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU, version 2 (GPL-2.0)
 * @link    https://emerchantpay.com
 */
class TransactionsHelper
{
    const SHIPPING_TOTAL_KEY = 'ot_shipping';

    /**
     * Retrieve Recurring Transaction Types
     *
     * @return array
     */
    public static function getRecurringTransactionTypes()
    {
        return [
            Types::INIT_RECURRING_SALE,
            Types::INIT_RECURRING_SALE_3D,
            Types::SDD_INIT_RECURRING_SALE
        ];
    }

    /**
     * Retrieve full language name by ISO code
     *
     * @param string $code ISO language code
     *
     * @return mixed|string
     */
    public static function getLanguageByIsoCode($code)
    {
        $languages = array(
            'en' => 'English',
            'it' => 'Italian',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'ja' => 'Japanese',
            'zh' => 'Mandarin Chinese',
            'ar' => 'Arabic',
            'pt' => 'Portuguese',
            'tr' => 'Turkish',
            'ru' => 'Russian',
            'hi' => 'Hindu',
            'bg' => 'Bulgarian',
            'id' => 'Indonesian',
            'ms' => 'Malay',
            'th' => 'Thai',
            'cs' => 'Czech',
            'hr' => 'Croatian',
            'sl' => 'Slovenian',
            'fi' => 'Finnish',
            'is' => 'Icelandic',
            'nl' => 'Dutch',
            'pl' => 'Polish'
        );

        if (array_key_exists($code, $languages)) {
            return $languages[$code];
        }

        return strtoupper($code);
    }

    /**
     * Build Invoice Items from Order
     *
     * @param \order $order Order details
     *
     * @return Items
     * @throws ErrorParameter|InvalidArgument
     */
    public static function getInvoiceCustomParamItems($order)
    {
        $items = new Items();
        $items->setCurrency($order->info['currency']);

        foreach ($order->products as $product) {
            $productType = $product['products_virtual'] ?
                ItemTypes::DIGITAL : ItemTypes::PHYSICAL;

            $invoiceItem = new Item();
            $invoiceItem
                ->setName($product['name'])
                ->setItemType($productType)
                ->setQuantity($product['qty'])
                ->setUnitPrice($product['final_price']);

            $items->addItem($invoiceItem);
        }

        $taxes = floatval($order->info['tax']);
        if ($taxes) {
            $invoiceItem = new Item();
            $invoiceItem
                ->setName('Taxes')
                ->setItemType(ItemTypes::SURCHARGE)
                ->setQuantity(1)
                ->setUnitPrice($taxes);

            $items->addItem($invoiceItem);
        }

        $shippingCost = array_key_exists('shipping_cost', $order->info) ?
            $order->info['shipping_cost'] :
            static::getShippingValueFromTotals($order->totals);
        if ($shippingCost) {
            $invoiceItem = new Item();
            $invoiceItem
                ->setName('Shipping Costs')
                ->setItemType(ItemTypes::SHIPPING_FEE)
                ->setQuantity(1)
                ->setUnitPrice($shippingCost);

            $items->addItem($invoiceItem);
        }

        return $items;
    }

    /**
     * Sanitize the Credit Card Number before Genesis Submission
     *
     * @param string $number Credit Card Number
     *
     * @return string
     */
    public static function sanitizeCreditCardNumber($number)
    {
        return str_replace(
            ' ',
            '',
            $number
        );
    }

    /**
     * Retrieve the Shipping value from Order Totals
     *
     * @param array $totals Order Totals
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return float
     */
    private static function getShippingValueFromTotals($totals)
    {
        foreach ($totals as $total) {
            if ($total['class'] === self::SHIPPING_TOTAL_KEY) {
                return $total['value'];
            }
        }

        return 0;
    }
}
