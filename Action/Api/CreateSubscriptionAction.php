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
use Antqa\Payum\Braintree\Request\Api\CreateCustomer;
use Antqa\Payum\Braintree\Request\Api\CreateSubscription;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

class CreateSubscriptionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateSubscription */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $response = $this->api->getBraintree()->subscription()->create($model->toUnsafeArrayWithoutLocal());

        if ($response->success) {
            $request->setSubscription($response->subscription);
        } else {
            throw new LogicException($response->message);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateSubscription &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
