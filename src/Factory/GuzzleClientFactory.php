<?php declare(strict_types=1);
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */
namespace OAT\Library\CorrelationIdsGuzzle\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use OAT\Library\CorrelationIdsGuzzle\Middleware\CorrelationIdsGuzzleMiddleware;

class GuzzleClientFactory
{
    /** @var CorrelationIdsGuzzleMiddleware */
    private $middleware;

    public function __construct(CorrelationIdsGuzzleMiddleware $middleware)
    {
        $this->middleware = $middleware;
    }

    public function create(array $configuration = []): Client
    {
        $configuration['handler'] = $this->pushMiddleware($configuration['handler'] ?? HandlerStack::create());

        return new Client($configuration);
    }

    private function pushMiddleware(HandlerStack $handlerStack): HandlerStack
    {
        $handlerStack->push(Middleware::mapRequest($this->middleware), CorrelationIdsGuzzleMiddleware::class);

        return $handlerStack;
    }
}
