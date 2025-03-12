<?php

namespace App\Tests\Unit\Service\Movie;

use App\Data\Movie\MovieData;
use App\Service\Movie\FetchMoviesByGenreService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FetchMoviesByGenreServiceTest extends TestCase
{
    private const API_KEY = 'test_api_key';

    public function testFetchMoviesByGenre(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $response = $this->createResponseMock();
        $fetchMoviesByGenreService = $this->createFetchMovieByGenreService(
            $httpClient,
            $logger,
        );

        $response
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);

        $response
            ->method('toArray')
            ->willReturn(['results' => [
                [
                    'id' => 1,
                    'title' => 'Movie 1',
                    'poster_path' => '/poster1.jpg',
                    'release_date' => '2021-01-01',
                    'overview' => 'Overview 1',
                    'adult' => false,
                    'backdrop_path' => '/backdrop1.jpg',
                    'original_language' => 'en',
                    'original_title' => 'Original Title 1',
                    'popularity' => 87,
                    'vote_average' => 8.5,
                    'vote_count' => 100,
                    'video' => false,
                    'genre_ids' => [21, 54],
                ],
                [
                    'id' => 2,
                    'title' => 'Movie 2',
                    'poster_path' => '/poster2.jpg',
                    'release_date' => '2021-01-02',
                    'overview' => 'Overview 2',
                    'adult' => false,
                    'backdrop_path' => '/backdrop2.jpg',
                    'original_language' => 'en',
                    'original_title' => 'Original Title 2',
                    'popularity' => 88,
                    'vote_average' => 8.6,
                    'vote_count' => 200,
                    'video' => false,
                    'genre_ids' => [21, 534],
                ],
            ]]);

        $httpClient->method('request')->willReturn($response);

        /** @var array<MovieData> $movies */
        $movies = $fetchMoviesByGenreService->execute([21, 54]);

        $this->assertCount(2, $movies);
        $this->assertInstanceOf(MovieData::class, $movies[0]);
        $this->assertInstanceOf(MovieData::class, $movies[1]);
        $this->assertSame(1, $movies[0]->getId());
        $this->assertSame('Movie 1', $movies[0]->getTitle());
        $this->assertSame(2, $movies[1]->getId());
        $this->assertSame('Movie 2', $movies[1]->getTitle());
    }

    public function testExceptionThrownDueToEmptyGenreIds(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $fetchMoviesByGenreService = $this->createFetchMovieByGenreService(
            $httpClient,
            $logger,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one genre ID must be provided.');

        $fetchMoviesByGenreService->execute([]);
    }

    public function testExceptionThrownDueToInvalidPage(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $fetchMoviesByGenreService = $this->createFetchMovieByGenreService(
            $httpClient,
            $logger,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be a positive integer.');

        $fetchMoviesByGenreService->execute([21], 0);
    }

    public function testExceptionThrownDueToInvalidStatusCode(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $response = $this->createResponseMock();
        $fetchMoviesByGenreService = $this->createFetchMovieByGenreService(
            $httpClient,
            $logger,
        );

        $response
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_INTERNAL_SERVER_ERROR);

        $httpClient->method('request')->willReturn($response);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Failed to fetch movies by genre.');

        $fetchMoviesByGenreService->execute([21, 54]);
    }

    public function testExceptionThrownDueToInvalidApiResponse(): void
    {
        $httpClient = $this->createHttpClientMock();
        $logger = $this->createLoggerMock();
        $response = $this->createResponseMock();
        $fetchMoviesByGenreService = $this->createFetchMovieByGenreService(
            $httpClient,
            $logger,
        );

        $response
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);
        $response
            ->method('toArray')
            ->willReturn([]);

        $httpClient
            ->method('request')
            ->willReturn($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid API response: missing "results" key.');

        $fetchMoviesByGenreService->execute([21, 54]);
    }

    private function createHttpClientMock(): HttpClientInterface&MockObject
    {
        return $this->getMockBuilder(HttpClientInterface::class)
            ->getMock();
    }

    private function createLoggerMock(): LoggerInterface&MockObject
    {
        return $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
    }

    private function createResponseMock(): ResponseInterface&MockObject
    {
        return $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
    }

    private function createFetchMovieByGenreService(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
    ): FetchMoviesByGenreService {
        return new FetchMoviesByGenreService(
            $httpClient,
            $logger,
            self::API_KEY,
        );
    }
}
