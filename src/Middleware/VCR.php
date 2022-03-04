<?php

declare(strict_types=1);

namespace Dragonmantank\VCR\Middleware;

use Dragonmantank\VCR\Tape;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VCR implements MiddlewareInterface
{
    public function __construct(protected string $libraryPath)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $tape = new Tape($request, $response);
        file_put_contents($this->libraryPath . '/' . $tape->getTapeID()->toString(), (string) $tape);

        return $response;
    }
}
