<?php

/*
 * This file is part of the antqa/payum-braintree package.
 *
 * (c) ant.qa <https://www.ant.qa>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antqa\Payum\Braintree;

use Antqa\Payum\Braintree\Action\Api\CreateCustomerAction;
use Antqa\Payum\Braintree\Action\Api\CreatePaymentMethodAction;
use Antqa\Payum\Braintree\Action\Api\CreateSubscriptionAction;
use Antqa\Payum\Braintree\Action\Api\DeletePaymentMethodAction;
use Antqa\Payum\Braintree\Action\Api\FindCustomerAction;
use Antqa\Payum\Braintree\Action\Api\FindPaymentMethodAction;
use Antqa\Payum\Braintree\Action\Api\FindSubscriptionAction;
use Antqa\Payum\Braintree\Action\Api\UpdateSubscriptionAction;
use Antqa\Payum\Braintree\Action\NotifyNullAction;
use Payum\Core\GatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Antqa\Payum\Braintree\Action\CaptureAction;
use Antqa\Payum\Braintree\Action\ConvertPaymentAction;
use Antqa\Payum\Braintree\Action\StatusAction;
use Antqa\Payum\Braintree\Action\Api\CreateTokenAction;

/**
 * @author Piotr Antosik <piotr.antosik@ant.qa>
 */
class BraintreeGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'Braintree',
            'payum.factory_title' => 'Braintree',
            'payum.action.capture' => new CaptureAction(),
          //  'payum.action.notify' => new NotifyAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.api.create_token' => new CreateTokenAction(),
            'payum.action.api.create_customer' => new CreateCustomerAction(),
            'payum.action.api.find_customer' => new FindCustomerAction(),
            'payum.action.api.find_subscription' => new FindSubscriptionAction(),
            'payum.action.api.find_payment_method' => new FindPaymentMethodAction(),
            'payum.action.api.create_payment_method' => new CreatePaymentMethodAction(),
            'payum.action.api.delete_payment_method' => new DeletePaymentMethodAction(),
            'payum.action.api.create_subscription' => new CreateSubscriptionAction(),
            'payum.action.api.update_subscription' => new UpdateSubscriptionAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'sandbox' => false,
                'merchantId' => null,
                'publicKey' => null,
                'privateKey' => null,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['merchantId', 'publicKey', 'privateKey'];
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
