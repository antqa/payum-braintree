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

use Braintree\Subscription;
use Payum\Core\Request\Generic;

class CreateSubscription extends Generic
{
    /**
     * @var Subscription|null
     */
    protected $subscription;

    /**
     * @param Subscription|null $subscription
     */
    public function setSubscription(Subscription $subscription = null)
    {
        $this->subscription = $subscription;
    }

    /**
     * @return Subscription|null
     */
    public function getSubscription()
    {
        return $this->subscription;
    }
}
