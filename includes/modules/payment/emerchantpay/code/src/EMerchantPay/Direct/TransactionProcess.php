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
use \EMerchantPay\Direct\Settings as EMerchantPayDirectSettings;

class TransactionProcess extends \EMerchantPay\Base\TransactionProcess
{

    /**
     * Set Genesis Config Values (Ex. Login, Password, Token, etc)
     */
    protected static function doLoadGenesisPrivateConfigValues()
    {
        parent::doLoadGenesisPrivateConfigValues();

        if (EMerchantPayDirectSettings::getIsConfigured()) {
            \Genesis\Config::setUsername(
                EMerchantPayDirectSettings::getUserName()
            );

            \Genesis\Config::setPassword(
                EMerchantPayDirectSettings::getPassword()
            );

            \Genesis\Config::setToken(
                EMerchantPayDirectSettings::getToken()
            );

            \Genesis\Config::setEnvironment(
                EMerchantPayDirectSettings::getIsLiveMode()
                    ? \Genesis\API\Constants\Environments::PRODUCTION
                    : \Genesis\API\Constants\Environments::STAGING
            );
        }
    }

    /**
     * Check - transaction is Asynchronous
     *
     * @param string $transactionType
     *
     * @return boolean
     */
    public static function isAsyncTransaction($transactionType )
    {
        return in_array($transactionType, array(
            \Genesis\API\Constants\Transaction\Types::AUTHORIZE_3D,
            \Genesis\API\Constants\Transaction\Types::SALE_3D
        ));
    }

    /**
     * Send transaction to Genesis
     *
     * @param $data array Transaction Data
     * @return \stdClass
     * @throws \Exception
     * @throws \Genesis\Exceptions\ErrorAPI
     */
    public static function pay($data)
    {
        switch ($data->transaction_type) {
            default:
            case \Genesis\API\Constants\Transaction\Types::AUTHORIZE:
                $genesis = new \Genesis\Genesis('Financial\Cards\Authorize');
                break;
            case \Genesis\API\Constants\Transaction\Types::AUTHORIZE_3D:
                $genesis = new \Genesis\Genesis('Financial\Cards\Authorize3D');
                break;
            case \Genesis\API\Constants\Transaction\Types::SALE:
                $genesis = new \Genesis\Genesis('Financial\Cards\Sale');
                break;
            case \Genesis\API\Constants\Transaction\Types::SALE_3D:
                $genesis = new \Genesis\Genesis('Financial\Cards\Sale3D');
                break;
        }

        if (isset($genesis)) {
            $genesis
                ->request()
                    ->setTransactionId( $data->transaction_id )
                    ->setRemoteIp(
                        EMerchantPayCommon::getServerRemoteAddress()
                    )
                    ->setUsage( 'ZenCart Electronic Transaction' )
                    ->setCurrency( $data->currency )
                    ->setAmount( $data->order->info['total'] )
                    ->setCardHolder( $data->card_info['cc_owner'] )
                    ->setCardNumber( $data->card_info['cc_number'] )
                    ->setExpirationYear( $data->card_info['cc_expiry_year'] )
                    ->setExpirationMonth( $data->card_info['cc_expiry_month'] )
                    ->setCvv( $data->card_info['cc_cvv'] )
                    ->setCustomerEmail( $data->order->customer['email_address'] )
                    ->setCustomerPhone( $data->order->customer['telephone'] )
                    ->setBillingFirstName( $data->order->billing['firstname'] )
                    ->setBillingLastName( $data->order->billing['lastname'] )
                    ->setBillingAddress1( $data->order->billing['street_address'] )
                    ->setBillingZipCode( $data->order->billing['postcode'] )
                    ->setBillingCity( $data->order->billing['city'] )
                    ->setBillingState( self::getStateCode($data->order->billing) )
                    ->setBillingCountry( $data->order->billing['country']['iso_code_2'] )
                    ->setShippingFirstName( $data->order->delivery['firstname'] )
                    ->setShippingLastName( $data->order->delivery['lastname'] )
                    ->setShippingAddress1( $data->order->delivery['street_address'] )
                    ->setShippingZipCode( $data->order->delivery['postcode'] )
                    ->setShippingCity( $data->order->delivery['city'] )
                    ->setShippingState( self::getStateCode($data->order->delivery) )
                    ->setShippingCountry( $data->order->delivery['country']['iso_code_2'] );

            if (isset($data->urls)) {
                $genesis
                    ->request()
                        ->setNotificationUrl( $data->urls['notification'] )
                        ->setReturnSuccessUrl( $data->urls['return_success'] )
                        ->setReturnFailureUrl( $data->urls['return_failure'] );
            }

            $genesis->execute();

            return $genesis->response()->getResponseObject();
        } else {
            return null;
        }
    }
}
