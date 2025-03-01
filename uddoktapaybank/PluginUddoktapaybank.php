<?php

require_once 'modules/admin/models/GatewayPlugin.php';
require_once 'plugins/gateways/uddoktapaybank/UddoktaPayBank.php';

class PluginUddoktaPayBank extends GatewayPlugin
{
    public function getVariables()
    {
        return [
            lang("Plugin Name") => [
                "type"        => "hidden",
                "description" => "",
                "value"       => "UddoktaPayBank"
            ],
            lang('Signup Name') => [
                'type'        => 'text',
                'description' => lang('Select the name to display in the signup process for this payment type. Example: eCheck or Credit Card.'),
                'value'       => 'UddoktaPay Bank'
            ],
            lang("API KEY") => [
                "type"        => "text",
                "description" => "Enter your API KEY",
                "value"       => ""
            ],
            lang("API URL") => [
                "type"        => "text",
                "description" => "Enter your API URL",
                "value"       => ""
            ],
            lang("Exchange Rate") => [
                "type"        => "text",
                "description" => "Exchange Rate (1 USD = ? BDT)",
                "value"       => ""
            ]
        ];
    }

    public function singlePayment($params)
    {
        $apiKey = $params['plugin_uddoktapaybank_API KEY'];
        $apiBaseURL = $params['plugin_uddoktapaybank_API URL'];
        $uddoktaPay = new UddoktaPayBank($apiKey, $apiBaseURL);

        $baseURL = rtrim(CE_Lib::getSoftwareURL(), '/') . '/';
        $callbackURL = $baseURL . "plugins/gateways/uddoktapaybank/callback.php";
        $cancelURL = $params['invoiceviewURLCancel'];


        $invoiceId = $params['invoiceNumber'];
        $amount = round($params["invoiceTotal"], 2);
        $firstname = $params['userFirstName'];
        $lastname = $params['userLastName'];
        $email = $params['userEmail'];
        $currencyCode = $params['userCurrency'];
        $exchangeRate = !empty($params['plugin_uddoktapaybank_Exchange Rate']) ? $params['plugin_uddoktapaybank_Exchange Rate'] : 1;

        if ($currencyCode !== 'BDT') {
            $amount *= $exchangeRate;
        }

        $requestData = [
            'full_name'    => "$firstname $lastname",
            'email'        => $email,
            'amount'       => $amount,
            'metadata'     => [
                'invoice_id' => $invoiceId,
                'currency'   => $currencyCode
            ],
            'redirect_url' => $callbackURL,
            'return_type'  => 'GET',
            'cancel_url'   => $cancelURL,
            'webhook_url'  => $callbackURL
        ];

        try {
            $paymentUrl = $uddoktaPay->initPayment($requestData);
            header('Location:' . $paymentUrl);
            exit();
        } catch (Exception $e) {
            die("Initialization Error: " . $e->getMessage());
        }
    }

    public function credit($params)
    {
    }
}
