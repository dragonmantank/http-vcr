<?php

declare(strict_types=1);

namespace Dragonmantank\VCR;

use Dragonmantank\VCR\Exception\InvalidFileFormatException;
use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\Response\Serializer as ResponseSerializer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Tape implements \Stringable
{
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly ResponseInterface $response,
        protected ?UuidInterface $tapeID = null
    ) {
    }

    static public function fromFile(string $path): static
    {
        $contents = file_get_contents($path);
        return static::fromString($contents);
    }

    static public function fromString(string $data): static
    {
        $chunks = preg_split(
            pattern: '/------------([0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12})[\r\n]/i',
            subject: $data,
            flags: PREG_SPLIT_DELIM_CAPTURE
        );

        if (count($chunks) !== 3) {
            throw new InvalidFileFormatException('Tape data was in an invalid format');
        }

        $request = Serializer::fromString(trim($chunks[0]));
        $response = ResponseSerializer::fromString(trim($chunks[2]));

        return new self($request, $response, Uuid::fromString(trim($chunks[1])));
    }

    public function getTapeID(): UuidInterface
    {
        if (!$this->tapeID) {
            $this->tapeID = Uuid::uuid4();
        }

        return $this->tapeID;
    }

    protected function setTapeID(string $id): void
    {
        $this->tapeID = $id;
    }

    public function __toString(): string
    {
        $string = Serializer::toString($this->request)
            . PHP_EOL
            . '------------'
            . $this->getTapeID()->toString()
            . PHP_EOL
            . ResponseSerializer::toString($this->response);

        return $string;
    }
}
