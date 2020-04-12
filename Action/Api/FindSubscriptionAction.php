<?php

/*
 * This file is part of the antqa/payum-braintree package.
 *
 * (c) ant.qa <https://www.ant.qa>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antqa\Payum\Braintree\Action\Api;

use Antqa\Payum\Braintree\Api;
use Antqa\Payum\Braintree\Request\Api\FindSubscription;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Storage\IdentityInterface;

class FindSubscriptionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request FindSubscription */
        RequestNotSupportedException::assertSupports($this, $request);

        $subscription = $this->api->getBraintree()->subscription()->find($request->getModel()->getId());
        $request->setSubscription($subscription);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof FindSubscription &&
            $request->getModel() instanceof IdentityInterface
        ;
    }
}
