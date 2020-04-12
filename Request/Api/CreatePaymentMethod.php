<?php

/*
 * This file is part of the antqa/payum-braintree package.
 *
 * (c) ant.qa <https://www.ant.qa>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antqa\Payum\Braintree\Request\Api;

use Payum\Core\Request\Generic;

class CreatePaymentMethod extends Generic
{
    /**
     * @var mixed|null
     */
    protected $paymentMethod;

    /**
     * @param mixed|null
     */
    public function setPaymentMethod($paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return mixed|null
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

}
