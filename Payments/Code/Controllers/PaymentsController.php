<?php

/*
 * This file is part of Kazist Framework.
 * (c) Dedan Irungu <irungudedan@gmail.com>
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * 
 */

/**
 * Description of PaymentsController
 *
 * @author sbc
 */

namespace Pesapal\Payments\Code\Controllers;

defined('KAZIST') or exit('Not Kazist Framework');

use Kazist\Controller\BaseController;
use Pesapal\Payments\Code\Models\PesapalModel;
use Pesapal\Payments\Code\Controllers\PaymentsController;

class PaymentsController extends PaymentsController {

    public function cancelAction() {

        $payment_id = $this->request->query->get('pesapal_merchant_reference');

        $this->model = new PesapalModel();
        $this->model->cancelTransaction($payment_id);
        $payment_url = $this->model->getUrlByPaymentId($payment_id);

        
        return $this->redirect($payment_url);
    }

    public function returnAction() {
      
        $payment_id = $this->request->query->get('pesapal_merchant_reference');

        $this->model = new PesapalModel();
        $this->model->completeTransaction($payment_id);
        
        $payment_url = $this->model->getUrlByPaymentId($payment_id);

        return $this->redirect($payment_url);
    }

    public function notifyAction() {

        $payment_id = $this->request->query->get('pesapal_merchant_reference');

        $this->model->processPesapal($payment_id);
        $this->model->notificationTransaction($payment_id);
        $payment_url = $this->model->getUrlByPaymentId($payment_id);

        return $this->redirect($payment_url);
    }

}
