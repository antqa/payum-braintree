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

use Braintree\Subscription;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\ApiAwareTrait;

use Antqa\Payum\Braintree\Api;

/**
 * @author Piotr Antosik <piotr.antosik@ant.qa>
 */
class StatusAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model[Api::FIELD_STATUS])) {
            $request->markNew();

            return;
        }

        if ($model[Api::FIELD_STATUS] && $model[Api::FIELD_STATUS] === Subscription::PENDING) {
            $request->markPending();

            return;
        }

        if ($model[Api::FIELD_STATUS] && $model[Api::FIELD_STATUS] === Subscription::CANCELED) {
            $request->markCanceled();

            return;
        }

        if ($model[Api::FIELD_STATUS] && $model[Api::FIELD_STATUS] === Subscription::ACTIVE) {
            $request->markCaptured();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
