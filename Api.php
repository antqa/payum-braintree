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
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;
use Http\Message\MessageFactory;
use Braintree\Gateway;

/**
 * @author Piotr Antosik <piotr.antosik@ant.qa>
 */
class Api
{
    const TRANSACTION_STATE_COMPLETED = 'Completed';

    //braintree
    const FIELD_TRANSACTION_TYPE = 'transaction_type';
    const FIELD_STATUS = 'status';
    const FIELD_ID = 'id';
    const FIELD_PRICE = 'price';
    const FIELD_TRIAL_DURATION = 'trialDuration';
    const FIELD_TRIAL_PERIOD = 'trialPeriod';
    const FIELD_BT_SIGNATURE = 'bt_signature';
    const FIELD_BT_PAYLOAD = 'bt_payload';

    const TRANSACTION_TYPE_SALE = 'sale';
    const TRANSACTION_TYPE_SUBSCRIPTION = 'subscription';

    const FIELD_PAYMENT_METHOD_TOKEN = 'paymentMethodToken';
    const FIELD_PLAN_ID = 'planId';
    const FIELD_DISCOUNTS = 'discounts';
    const FIELD_MERCHANT_ACCOUNT = 'merchantAccountId';

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var \Braintree\Gateway
     */
    protected $braintree;

    /**
     * @var array
     */
    protected $options = [
        'sandbox' => false,
        'merchantId' => null,
        'publicKey' => null,
        'privateKey' => null,
    ];

    /**
     * @param array $options
     * @param HttpClientInterface $client
     * @param MessageFactory $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client = null, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty([
            'merchantId',
            'publicKey',
            'privateKey',
        ]);

        if (!is_bool($options['sandbox'])) {
            throw new LogicException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client ?: HttpClientFactory::create();
        $this->messageFactory = $messageFactory;
//        $this->braintree = new Gateway([
//            // When in testMode, use the sandbox environment
//            'environment' => $this->isSandbox() ? 'sandbox' : 'production',
//            'merchantId' => $this->options['merchantId'],
//            'publicKey' => $this->options['publicKey'],
//            'privateKey' => $this->options['privateKey'],
//        ]);

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

    /**
     * @param array $params
     *
     * @return array
     */
    public function preparePayment(array $params)
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

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $this->addRequiredParams($params);

        return $params;
    }

    /**
     * @param array $params
     */
    protected function addRequiredParams(array &$params)
    {
        $params[self::FIELD_MERCHANT] = $this->options['merchant_account'];
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    public function resendToken(array $fields)
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $request = $this->messageFactory->createRequest('POST', $this->getIPNEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return 'https://secure.payza.com/checkout';
    }

    /**
     * @return string
     */
    public function getIPNEndpoint()
    {
        return 'https://secure.payza.com/ipn2.ashx';
    }

    /**
     * @return string
     */
    public function getCaptureRedirect()
    {
        return $this->options['capture_redirect'];
    }

    /**
     * @return bool
     */
    public function isSandbox()
    {
        return $this->options['sandbox'];
    }

    /**
     * @return Gateway
     */
    public function getBraintree(): Gateway
    {
        return $this->braintree;
    }

    /**
     * @param array $parameters
     *
     * @return \Braintree_WebhookNotification
     *
     * @throws \Braintree_Exception_InvalidSignature
     */
    public function parseNotification(array $parameters = array())
    {
        return $this->getBraintree()->webhookNotification()->parse(
            $parameters[self::FIELD_BT_SIGNATURE],
            $parameters[self::FIELD_BT_PAYLOAD]
        );
    }

    /**
     * @param array $parameters
     *
     * @return bool
     */
    public function hasNotificationParameters(array $parameters = array())
    {
        return array_key_exists(self::FIELD_BT_SIGNATURE, $parameters) && array_key_exists(self::FIELD_BT_PAYLOAD, $parameters);
    }
}
