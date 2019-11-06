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
namespace OAT\Library\CorrelationIdsGuzzle\Tests\Integration\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use OAT\Library\CorrelationIds\Provider\CorrelationIdsHeaderNamesProviderInterface;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistryInterface;
use OAT\Library\CorrelationIdsGuzzle\Middleware\CorrelationIdsGuzzleMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CorrelationIdsGuzzleMiddlewareTest extends TestCase
{
    /** @var CorrelationIdsRegistryInterface|MockObject */
    private $registryMock;

    /** @var CorrelationIdsHeaderNamesProviderInterface|MockObject */
    private $providerMock;

    /** @var array */
    private $history = [];

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(CorrelationIdsRegistryInterface::class);
        $this->providerMock = $this->createMock(CorrelationIdsHeaderNamesProviderInterface::class);
    }

    protected function tearDown(): void
    {
        $this->history = [];
    }

    public function testItForwardsCorrelationIdsAsRequestHeaders(): void
    {
        $this->registryMock
            ->expects($this->exactly(2))
            ->method('getCurrentCorrelationId')
            ->willReturn('current');

        $this->registryMock
            ->expects($this->once())
            ->method('getParentCorrelationId')
            ->willReturn('parent');

        $this->registryMock
            ->expects($this->once())
            ->method('getRootCorrelationId')
            ->willReturn('root');

        $this->providerMock
            ->expects($this->never())
            ->method('provideCurrentCorrelationIdHeaderName');

        $this->providerMock
            ->expects($this->once())
            ->method('provideParentCorrelationIdHeaderName')
            ->willReturn(CorrelationIdsHeaderNamesProviderInterface::DEFAULT_PARENT_CORRELATION_ID_HEADER_NAME);

        $this->providerMock
            ->expects($this->once())
            ->method('provideRootCorrelationIdHeaderName')
            ->willReturn(CorrelationIdsHeaderNamesProviderInterface::DEFAULT_ROOT_CORRELATION_ID_HEADER_NAME);

        $client = $this->prepareTestGuzzleClient([new Response()]);
        $client->request('POST', 'http://example.com');

        $executedRequestHeaders = current($this->history)['request']->getHeaders();

        $this->assertEquals(
            ['current'],
            $executedRequestHeaders[CorrelationIdsHeaderNamesProviderInterface::DEFAULT_PARENT_CORRELATION_ID_HEADER_NAME]
        );

        $this->assertEquals(
            ['root'],
            $executedRequestHeaders[CorrelationIdsHeaderNamesProviderInterface::DEFAULT_ROOT_CORRELATION_ID_HEADER_NAME]
        );
    }

    private function prepareTestGuzzleClient(array $expectedResponses): Client
    {
        $handlerStack = HandlerStack::create(new MockHandler($expectedResponses));

        $handlerStack->push(
            Middleware::mapRequest(new CorrelationIdsGuzzleMiddleware($this->registryMock, $this->providerMock))
        );

        $handlerStack->push(
            Middleware::history($this->history)
        );

        return new Client(['handler' => $handlerStack]);
    }
}
