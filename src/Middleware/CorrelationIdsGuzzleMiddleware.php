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
namespace OAT\Library\CorrelationIdsGuzzle\Middleware;

use OAT\Library\CorrelationIds\Provider\CorrelationIdsHeaderNamesProvider;
use OAT\Library\CorrelationIds\Provider\CorrelationIdsHeaderNamesProviderInterface;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistryInterface;
use Psr\Http\Message\RequestInterface;

class CorrelationIdsGuzzleMiddleware
{
    /** @var CorrelationIdsRegistryInterface */
    private $registry;

    /** @var CorrelationIdsHeaderNamesProviderInterface */
    private $provider;

    public function __construct(
        CorrelationIdsRegistryInterface $registry,
        CorrelationIdsHeaderNamesProviderInterface $provider = null
    ) {
        $this->registry = $registry;
        $this->provider = $provider ?? new CorrelationIdsHeaderNamesProvider();
    }

    public function __invoke(RequestInterface $request, array $options = []): RequestInterface
    {
        $headers = [
            $this->provider->provideParentCorrelationIdHeaderName() => $this->registry->getCurrentCorrelationId(),
            $this->provider->provideRootCorrelationIdHeaderName() => $this->determinateRootCorrelationId(),
        ];

        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request;
    }

    private function determinateRootCorrelationId(): string
    {
        $candidates = array_filter([
            $this->registry->getRootCorrelationId(),
            $this->registry->getParentCorrelationId(),
            $this->registry->getCurrentCorrelationId(),
        ]);

        return array_shift($candidates) ?? '';
    }
}
