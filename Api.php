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

use Braintree\Configuration;
use Braintree\WebhookNotification;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;
use Http\Message\MessageFactory;
use Braintree\Gateway;
use Braintree\Exception;

/**
 * @author Piotr Antosik <piotr.antosik@ant.qa>
 */
class Api
{
    const TRANSACTION_STATE_COMPLETED = 'Completed';

    public const FIELD_TRANSACTION_TYPE = 'transaction_type';
    public const FIELD_STATUS = 'status';
    public const FIELD_ID = 'id';
    public const FIELD_PRICE = 'price';
    public const FIELD_TRIAL_DURATION = 'trialDuration';
    public const FIELD_TRIAL_PERIOD = 'trialPeriod';
    public const FIELD_BT_SIGNATURE = 'bt_signature';
    public const FIELD_BT_PAYLOAD = 'bt_payload';

    public const TRANSACTION_TYPE_SALE = 'sale';
    public const TRANSACTION_TYPE_SUBSCRIPTION = 'subscription';

    public const FIELD_PAYMENT_METHOD_TOKEN = 'paymentMethodToken';
    public const FIELD_PLAN_ID = 'planId';
    public const FIELD_DISCOUNTS = 'discounts';
    public const FIELD_MERCHANT_ACCOUNT = 'merchantAccountId';

    private HttpClientInterface $client;

    private Gateway $braintree;

    /**
     * @var array|ArrayObject
     */
    private $options = [
        'sandbox' => false,
        'merchantId' => null,
        'publicKey' => null,
        'privateKey' => null,
    ];

    /**
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client = null, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(
            [
                'merchantId',
                'publicKey',
                'privateKey',
            ]
        );

        if (!\is_bool($options['sandbox'])) {
            throw new LogicException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client ?: HttpClientFactory::create();
        $this->messageFactory = $messageFactory;

        $this->braintree = Configuration::gateway();

        // When in testMode, use the sandbox environment
        if ($this->isSandbox()) {
            $this->braintree->config->environment('sandbox');
        } else {
            $this->braintree->config->environment('production');
        }
        // Set the keys
        $this->braintree->config->merchantId($this->options['merchantId']);
        $this->braintree->config->publicKey($this->options['publicKey']);
        $this->braintree->config->privateKey($this->options['privateKey']);
    }

    public function preparePayment(array $params): array
    {
        $supportedParams = [
            self::FIELD_MERCHANT => null,
            self::FIELD_PURCHASE_TYPE => null,
            self::FIELD_ITEM_NAME => null,
            self::FIELD_AMOUNT => null,
            self::FIELD_CURRENCY => null,
            self::FIELD_RETURN_URL => null,
            self::FIELD_ALERT_URL => null,
            self::FIELD_CANCEL_URL => null,
            self::FIELD_IPN_VERSION => null,
            self::FIELD_CUSTOM_1 => null,
            self::FIELD_CUSTOM_2 => null,
            self::FIELD_CUSTOM_3 => null,
            self::FIELD_CUSTOM_4 => null,
            self::FIELD_CUSTOM_5 => null,
            self::FIELD_CUSTOM_6 => null,
        ];

        $params = \array_filter(
            \array_replace(
                $supportedParams,
                \array_intersect_key($params, $supportedParams)
            )
        );

        $this->addRequiredParams($params);

        return $params;
    }

    protected function addRequiredParams(array &$params): void
    {
        $params[self::FIELD_MERCHANT] = $this->options['merchant_account'];
    }

    public function resendToken(array $fields): string
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $request = $this->messageFactory->createRequest(
            'POST',
            $this->getIPNEndpoint(),
            $headers,
            http_build_query($fields)
        );

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @return string
     */
    public function getCaptureRedirect()
    {
        return $this->options['capture_redirect'];
    }

    public function isSandbox(): bool
    {
        return $this->options['sandbox'];
    }

    public function getBraintree(): Gateway
    {
        return $this->braintree;
    }

    /**
     * @throws Exception\InvalidSignature
     */
    public function parseNotification(array $parameters = []): WebhookNotification
    {
        return $this->getBraintree()->webhookNotification()->parse(
            $parameters[self::FIELD_BT_SIGNATURE],
            $parameters[self::FIELD_BT_PAYLOAD]
        );
    }

    public function hasNotificationParameters(array $parameters = []): bool
    {
        return \array_key_exists(self::FIELD_BT_SIGNATURE, $parameters) && \array_key_exists(
                self::FIELD_BT_PAYLOAD,
                $parameters
            );
    }
}
