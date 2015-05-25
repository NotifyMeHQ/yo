<?php

/*
 * This file is part of NotifyMe.
 *
 * (c) Cachet HQ <support@cachethq.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NotifyMeHQ\Yo;

use GuzzleHttp\Client;
use NotifyMeHQ\NotifyMe\Arr;
use NotifyMeHQ\NotifyMe\FactoryInterface;

/**
 * This is the yo factory class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class YoFactory implements FactoryInterface
{
    /**
     * Create a new yo gateway instance.
     *
     * @param string[] $config
     *
     * @return \NotifyMeHQ\Yo\YoGateway
     */
    public function make(array $config)
    {
        Arr::requires($config, ['token']);

        $client = new Client();

        return new YoGateway($client, $config);
    }
}
