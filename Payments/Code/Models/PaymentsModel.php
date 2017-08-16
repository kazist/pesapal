<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Pesapal\Payments\Code\Models;

defined('KAZIST') or exit('Not Kazist Framework');

use Kazist\KazistFactory;
use Payments\Payments\Code\Models\PaymentsModel AS BasePaymentModel;
use Pesapal\Payments\Code\Classes\Pesapal;

/**
 * Description of MarketplaceModel
 *
 * @author sbc
 */
class PaymentsModel extends BasePaymentModel {

    public $code = '';

    public function appendSearchQuery($query) {

        $this->ingore_search_query = true;
        return parent:: appendSearchQuery($query);
    }

    public function notificationTransaction($payment_id) {

        $this->processPesapal($payment_id);
    }

    public function completeTransaction($payment_id) {
        $this->notificationTransaction($payment_id);
    }

    public function cancelTransaction($payment_id) {
        parent::cancelTransaction($payment_id);
    }

    public function processPesapal($payment_id) {

        $pesapal = new Pesapal();

        $parameters = array();
        $payment_method = 'pesapal';
        $payment = $this->getPaymentById($payment_id);
        $gateway = $this->getGatewayByShortName($payment_method);

        $parameters['consumer_key'] = $this->getGatewayParameter($gateway->id, 'consumer_key');
        $parameters['consumer_secret'] = $this->getGatewayParameter($gateway->id, 'consumer_secret');
        $parameters['statusrequestAPI'] = $this->getGatewayParameter($gateway->id, 'statusrequestAPI');

        $status = $pesapal->processReturns($parameters);

        if ($status == 'COMPLETED') {

            if ($payment->code == '') {

                $payment->type = 'pesapal';
                $payment->gateway_id = $gateway->id;
                $payment->code = $this->request->get('pesapal_transaction_tracking_id');
                $payment->receipt_no = $payment->receipt_no;

                parent::savePaidAmount($payment, $payment->amount, $payment->amount);

                parent::successfulTransaction($payment_id, $this->code);
            }
        } elseif ($status == "FAILED" || $status == "INVALID") {
            parent::failTransaction($payment_id);
        } else {
            parent::pendingTransaction($payment_id);
        }
    }

    public function getPaypalParams() {

        $posted_data = array();

        $factory = new KazistFactory();
        $input = $factory->getInput();

        $posted_data['pesapal_notification_type'] = $this->request->get('pesapal_notification_type');
        $posted_data['pesapal_transaction_tracking_id'] = $this->request->request->get('pesapal_transaction_tracking_id');
        $posted_data['pesapal_merchant_reference'] = $this->request->request->get('pesapal_merchant_reference');

        return $posted_data;
    }

}
