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

use Braintree\Subscription as SubscriptionObject;
use Payum\Core\Request\Generic;

abstract class Subscription extends Generic
{
    /**
     * @var SubscriptionObject|null
     */
    protected $subscription;

    /**
     * @param SubscriptionObject|null $subscription
     */
    public function setSubscription(SubscriptionObject $subscription = null)
    {
        $this->subscription = $subscription;
    }

    /**
     * @return SubscriptionObject|null
     */
    public function getSubscription()
    {
        return $this->subscription;
    }
}
