<?php

declare(strict_types=1);

/**
 * @project Legatus CGI Runtime
 * @link https://github.com/legatus-php/runtime-cgi
 * @package legatus/runtime-cgi
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license MIT
 * @copyright 2021 Matias Navarro-Carter
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Support;

use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CgiRuntimeTest.
 */
class CgiRuntimeTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testItRunsCGIRequest(): void
    {
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $createRequestMock = $this->createMock(ServerRequestCreatorInterface::class);
        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);

        $createRequestMock->expects(self::once())
            ->method('fromGlobals')
            ->willReturn($requestStub);
        $handlerMock->expects(self::once())
            ->method('handle')
            ->with($requestStub)
            ->willReturn($responseMock);
        $responseMock->expects(self::once())
            ->method('getProtocolVersion')
            ->willReturn('1.1');
        $responseMock->expects(self::exactly(2))
            ->method('getStatusCode')
            ->willReturn(200);
        $responseMock->expects(self::once())
            ->method('getReasonPhrase')
            ->willReturn('OK');
        $responseMock->expects(self::once())
            ->method('getHeaders')
            ->willReturn([
                'Content-Type' => ['text/plain'],
            ]);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn($streamMock);
        $streamMock->expects(self::once())
            ->method('isSeekable')
            ->willReturn(false);
        $streamMock->expects(self::atLeastOnce())
            ->method('eof')
            ->willReturnOnConsecutiveCalls(false, false, false, true);
        $streamMock->expects(self::exactly(3))
            ->method('read')
            ->with(1024 * 8)
            ->willReturnOnConsecutiveCalls('a', 'b', 'c');

        $cgiRuntime = new CgiRuntime($createRequestMock, $handlerMock);
        $cgiRuntime->run();
        $this->expectOutputString('abc');
    }
}
