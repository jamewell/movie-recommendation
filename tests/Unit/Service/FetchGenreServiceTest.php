<?php

namespace App\Tests\Unit\Service;

use App\Service\Movie\FetchGenreService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FetchGenreServiceTest extends TestCase
{
    private const API_KEY = 'test_api_key';

    public function testFetchGenres(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $response = $this->createResponseMock();
        $fetchGenreService = $this->createFetchGenreService(
            $httpClient,
            self::API_KEY,
            $logger,
        );

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('toArray')
            ->willReturn(['genres' => [
                ['id' => 1, 'name' => 'Action'],
                ['id' => 2, 'name' => 'Comedy'],
            ]]);

        $httpClient->method('request')->willReturn($response);

        $genres = $fetchGenreService->execute();

        $this->assertIsArray($genres);
        $this->assertCount(2, $genres);
        $this->assertArrayHasKey('id', $genres[0]);
        $this->assertArrayHasKey('name', $genres[0]);
        $this->assertSame('Action', $genres[0]['name']);
        $this->assertArrayHasKey('id', $genres[1]);
        $this->assertArrayHasKey('name', $genres[1]);
        $this->assertSame('Comedy', $genres[1]['name']);
    }

    public function testExceptionThrowDueToInvalidStatusCode(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $response = $this->createResponseMock();
        $fetchGenreService = $this->createFetchGenreService(
            $httpClient,
            self::API_KEY,
            $logger,
        );

        $response
            ->method('getStatusCode')
            ->willReturn(500);

        $httpClient->method('request')->willReturn($response);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Failed to fetch genres.');

        $fetchGenreService->execute();
    }

    public function testFetchGenresReturnsNull(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $response = $this->createResponseMock();
        $fetchGenreService = $this->createFetchGenreService(
            $httpClient,
            self::API_KEY,
            $logger,
        );

        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->method('toArray')
            ->willReturn([]);

        $httpClient->method('request')->willReturn($response);

        $genres = $fetchGenreService->execute();

        $this->assertNull($genres);
    }

    public function testConstructorThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch genres.');

        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $fetchGenreService = $this->createFetchGenreService(
            $httpClient,
            self::API_KEY,
            $logger,
        );

        $fetchGenreService->execute();
    }

    private function createFetchGenreService(
        HttpClientInterface $httpClient,
        string $api_key,
        LoggerInterface $logger,
    ): FetchGenreService {
        return new FetchGenreService($httpClient, $api_key, $logger);
    }

    private function createHttpClientMock(): HttpClientInterface&MockObject
    {
        return $this->getMockBuilder(HttpClientInterface::class)
            ->getMock();
    }

    private function createResponseMock(): ResponseInterface&MockObject
    {
        return $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
    }

    private function createLoggerMock(): LoggerInterface&MockObject
    {
        return $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
    }
}
