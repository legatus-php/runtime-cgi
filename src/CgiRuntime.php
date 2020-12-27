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

use function Http\Response\send;
use Nyholm\Psr7Server\ServerRequestCreatorInterface as CreateRequest;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * The CgiRuntime runs an application behind a FastCGI proxy.
 */
final class CgiRuntime implements Runtime
{
    /**
     * @var CreateRequest
     */
    private CreateRequest $createRequest;
    /**
     * @var Handler
     */
    private Handler $handler;

    /**
     * CgiRuntime constructor.
     *
     * @param CreateRequest $createRequest
     * @param Handler       $handler
     */
    public function __construct(CreateRequest $createRequest, Handler $handler)
    {
        $this->createRequest = $createRequest;
        $this->handler = $handler;
    }

    public function run(): void
    {
        $request = $this->createRequest->fromGlobals();
        $response = $this->handler->handle($request);
        send($response);
    }
}
