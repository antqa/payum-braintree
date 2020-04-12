<?php

/*
 * This file is part of the antqa/payum-braintree package.
 *
 * (c) ant.qa <https://www.ant.qa>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antqa\Payum\Braintree\Action;

use Antqa\Payum\Braintree\Request\Api\CreateSubscription;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

use Antqa\Payum\Braintree\Api;

/**
 * @author Piotr Antosik <piotr.antosik@ant.qa>
 */
class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
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
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

//        if (!isset($model[Api::FIELD_TRANSACTION_TYPE]) || $model[Api::FIELD_TRANSACTION_TYPE] !== Api::TRANSACTION_TYPE_SUBSCRIPTION) {
//            throw new LogicException('Missing transaction type');
//        }

        $this->gateway->execute($subscriptionRequest = new CreateSubscription($model));

        $model->replace([
            Api::FIELD_STATUS => $subscriptionRequest->getSubscription()->status,
            Api::FIELD_ID => $subscriptionRequest->getSubscription()->id,
            Api::FIELD_TRIAL_PERIOD => $subscriptionRequest->getSubscription()->trialPeriod,
            Api::FIELD_TRIAL_DURATION => $subscriptionRequest->getSubscription()->trialDuration,
            Api::FIELD_PRICE => $subscriptionRequest->getSubscription()->price,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
