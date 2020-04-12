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
use Antqa\Payum\Braintree\Request\Api\DeletePaymentMethod;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Storage\IdentityInterface;

class DeletePaymentMethodAction implements ActionInterface, ApiAwareInterface
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
        /** @var $request DeletePaymentMethod */
        RequestNotSupportedException::assertSupports($this, $request);

        $response = $this->api->getBraintree()->paymentMethod()->delete($request->getModel()->getId());

        if (!$response->success) {
            throw new \Braintree\Exception($response->message);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof DeletePaymentMethod &&
            $request->getModel() instanceof IdentityInterface
        ;
    }
}
