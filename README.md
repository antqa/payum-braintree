# Payum Perfect Money
[![Build Status](https://travis-ci.org/antqa/payum-braintree.png?branch=master)](https://travis-ci.org/antqa/payum-braintree)
[![Total Downloads](https://poser.pugx.org/antqa/payum-braintree/downloads)](https://packagist.org/packages/antqa/payum-braintree)
[![Latest Stable Version](https://poser.pugx.org/antqa/payum-braintree/v/stable)](https://packagist.org/packages/antqa/payum-braintree) 

The Payum extension. It provides [Perfect Money](https://perfectmoney.is) payment integration.

## Installation

```bash
$ composer require antqa/payum-braintree
```

## Configuration

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

$payum = (new PayumBuilder)
    ->addGatewayFactory('perfectmoney', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new \Antqa\Payum\Perfectmoney\PerfectMoneyGatewayFactory($config, $coreGatewayFactory)
    })
    ->addGateway('perfectmoney', [
        'factory' => 'perfectmoney',
        'sandbox' => true,
        'alternate_passphrase' => 'place here',
        'payee_account' => 'place here',
        'display_name' => 'place here',
    ])
    ->getPayum()
;
```

## Payment

### Additional parameters

```php
use Payum\Core\Model\PaymentInterface;
use Antqa\Payum\Perfectmoney\Api;

/** @var PaymentInterface $payment */
$payment->setDetails([
    Api::FIELD_SUGGESTED_MEMO => sprintf('Payment - %s', $product),
    Api::FIELD_PAYMENT_URL_METHOD = 'POST',
    Api::FIELD_NOPAYMENT_URL_METHOD = 'POST',
]);
```

## Symfony integration

```yml
#services.yml

app.payum.perfectmoney.factory:
    class: Antqa\Payum\Perfectmoney\PerfectMoneyGatewayFactory
    arguments: [[], '@payum.core_gateway_factory']
    tags:
        - { name: payum.gateway_factory, factory_name: perfectmoney, human_name: 'Perfect Money' }
```

### Configuration

```yml
#config.yml

payum:
    gateways_v2:
        perfectmoney:
            factory: perfectmoney
            payee_account: %perfectmoney_account%
            alternate_passphrase: %perfectmoney_alternate_passphrase%
            sandbox: %payment_sandbox%
            display_name: place_here
```

## License

Payum Perfect Money is released under the [MIT License](LICENSE).
