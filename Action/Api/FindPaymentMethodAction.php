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
use Antqa\Payum\Braintree\Request\Api\FindPaymentMethod;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Storage\IdentityInterface;

class FindPaymentMethodAction implements ActionInterface, ApiAwareInterface
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
        /** @var $request FindPaymentMethod */
        RequestNotSupportedException::assertSupports($this, $request);

        $paymentMethod = $this->api->getBraintree()->paymentMethod()->find($request->getModel()->getId());
        $request->setPaymentMethod($paymentMethod);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof FindPaymentMethod &&
            $request->getModel() instanceof IdentityInterface
        ;
    }
}
