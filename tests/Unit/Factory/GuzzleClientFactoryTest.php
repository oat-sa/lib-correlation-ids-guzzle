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
namespace OAT\Library\CorrelationIdsGuzzle\Tests\Unit\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use OAT\Library\CorrelationIdsGuzzle\Factory\GuzzleClientFactory;
use OAT\Library\CorrelationIdsGuzzle\Middleware\CorrelationIdsGuzzleMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuzzleClientFactoryTest extends TestCase
{
    /** @var CorrelationIdsGuzzleMiddleware|MockObject */
    private $middlewareMock;

    /** @var GuzzleClientFactory */
    private $subject;

    protected function setUp(): void
    {
        $this->middlewareMock = $this->createMock(CorrelationIdsGuzzleMiddleware::class);

        $this->subject = new GuzzleClientFactory($this->middlewareMock);
    }

    public function testItCanCreateAGuzzleClientAndReuseProvidedHandlerStackWithTheMiddlewareEnabled(): void
    {
        $result = $this->subject->create(['handler' => HandlerStack::create()]);

        $this->assertInstanceOf(Client::class, $result);

        $this->assertStringContainsString(
            CorrelationIdsGuzzleMiddleware::class,
            $result->getConfig('handler')->__toString()
        );
    }

    public function testItCanCreateAGuzzleClientAndCreateHandlerStackWithTheMiddlewareEnabled(): void
    {
        $result = $this->subject->create();

        $this->assertInstanceOf(Client::class, $result);

        $this->assertStringContainsString(
            CorrelationIdsGuzzleMiddleware::class,
            $result->getConfig('handler')->__toString()
        );
    }
}
