<?php

declare(strict_types=1);

namespace Dragonmantank\VCRTest;

use Dragonmantank\VCR\Exception\InvalidFileFormatException;
use Dragonmantank\VCR\Tape;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class TapeTest extends TestCase
{
    public function testCanLoadFromFile()
    {
        $tape = Tape::fromFile(__DIR__ . '/data/0f038004-fea2-4f89-ae11-a49cec09dbcf');
        $this->assertInstanceOf(Tape::class, $tape);
    }

    public function testInvalidFormatThrowsException()
    {
        $this->expectException(InvalidFileFormatException::class);

        Tape::fromString('This is a file---bleh');
    }

    public function testNewTapeGeneratesAnID()
    {
        $request = new Request('/test');
        $response = new Response();
        $tape = new Tape($request, $response);

        $this->assertInstanceOf(UuidInterface::class, $tape->getTapeID());
    }

    public function testTapeIDIsReusedWhenImported()
    {
        $request = new Request('/test');
        $response = new Response();
        $tape = new Tape($request, $response);
        $tape2 = Tape::fromString((string) $tape);

        $this->assertEquals($tape->getTapeID()->toString(), $tape2->getTapeID()->toString());
    }
}
