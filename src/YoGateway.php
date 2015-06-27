<?php

/*
 * This file is part of NotifyMe.
 *
 * (c) Alt Three LTD <support@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NotifyMeHQ\Yo;

use GuzzleHttp\Client;
use NotifyMeHQ\NotifyMe\Arr;
use NotifyMeHQ\Contracts\GatewayInterface;
use NotifyMeHQ\NotifyMe\HttpGatewayTrait;
use NotifyMeHQ\NotifyMe\Response;

/**
 * This is the yo gateway class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class YoGateway implements GatewayInterface
{
    use HttpGatewayTrait;

    /**
     * Gateway api endpoint.
     *
     * @var string
     */
    protected $endpoint = 'http://api.justyo.co';

    /**
     * The http client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Configuration options.
     *
     * @var string[]
     */
    protected $config;

    /**
     * Create a new yo gateway instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param string[]           $config
     *
     * @return void
     */
    public function __construct(Client $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Send a notification.
     *
     * @param string   $to
     * @param string   $message
     * @param string[] $options
     *
     * @return \NotifyMeHQ\Contracts\ResponseInterface
     */
    public function notify($to, $message = 'yo', array $options = [])
    {
        $params['username'] = strtoupper($to);

        $params['api_token'] = Arr::get($options, 'token', $this->config['token']);
        $params['location'] = Arr::get($options, 'location');
        $params['link'] = Arr::get($options, 'link');

        return $this->commit('post', $this->buildUrlFromString('yo'), $params);
    }

    /**
     * Commit a HTTP request.
     *
     * @param string   $method
     * @param string   $url
     * @param string[] $params
     * @param string[] $options
     *
     * @return mixed
     */
    protected function commit($method = 'post', $url, array $params = [], array $options = [])
    {
        $success = false;

        $rawResponse = $this->client->{$method}($url, [
            'exceptions'      => false,
            'timeout'         => '80',
            'connect_timeout' => '30',
            'body'            => $params,
        ]);

        if ($rawResponse->getStatusCode() == 200) {
            $response = $this->parseResponse($rawResponse->getBody());
            $success = $response['success'];
        } else {
            $response = $this->responseError($rawResponse);
        }

        return $this->mapResponse($success, $response);
    }

    /**
     * Map HTTP response to response object.
     *
     * @param bool  $success
     * @param array $response
     *
     * @return \NotifyMeHQ\Contracts\ResponseInterface
     */
    protected function mapResponse($success, $response)
    {
        return (new Response())->setRaw($response)->map([
            'success' => $success,
            'message' => $success ? 'Message sent' : $response['error'],
        ]);
    }

    /**
     * Get the default json response.
     *
     * @param string $rawResponse
     *
     * @return array
     */
    protected function jsonError($rawResponse)
    {
        $message = "API Response not valid. (Raw response API {$rawResponse->getBody()})";

        return ['error' => $message];
    }

    /**
     * Get the request url.
     *
     * @return string
     */
    protected function getRequestUrl()
    {
        return $this->endpoint;
    }
}
