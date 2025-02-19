<?php

namespace App\Tests\Unit\Service;

use App\Service\Movie\FetchGenreService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FetchGenreServiceTest extends TestCase
{
    public function testFetchGenres(): void
    {
        $httpClient = $this->createHttpClientMock();
        $param = $this->createContainerBagMock();
        $response = $this->createResponseMock();
        $fetchGenreService = $this->createFetchGenreService($httpClient, $param);

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

    public function testFetchGenresFailed(): void
    {
        $httpClient = $this->createHttpClientMock();
        $param = $this->createContainerBagMock();
        $response = $this->createResponseMock();
        $fetchGenreService = $this->createFetchGenreService($httpClient, $param);

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
        $param = $this->createContainerBagMock();
        $response = $this->createResponseMock();
        $fetchGenreService = $this->createFetchGenreService($httpClient, $param);

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
        $this->expectExceptionMessage('TMDB API key is missing: No tmdb_api_key found.');

        $httpClient = $this->createHttpClientMock();
        $param = $this->createContainerBagMockWithException();
        $fetchGenreService = $this->createFetchGenreService($httpClient, $param);

        $fetchGenreService->execute();
    }

    private function createFetchGenreService(HttpClientInterface $httpClient, ContainerBagInterface $param): FetchGenreService
    {
        return new FetchGenreService($httpClient, $param);
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

    private function createContainerBagMock(): ContainerBagInterface&MockObject
    {
        $param = $this->getMockBuilder(ContainerBagInterface::class)
            ->getMock();
        $param
            ->expects($this->once())
            ->method('get')
            ->with('tmdb_api_key')
            ->willReturn('test_api_key');

        return $param;
    }

    private function createContainerBagMockWithException(): ContainerBagInterface&MockObject
    {
        $param = $this->getMockBuilder(ContainerBagInterface::class)
            ->getMock();
        $param
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new \RuntimeException('No tmdb_api_key found.'));

        return $param;
    }
}
