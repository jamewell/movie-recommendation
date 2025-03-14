<?php

namespace App\Tests\Unit\Service\Movie;

use App\Data\Movie\MovieData;
use App\Entity\Genre;
use App\Entity\User;
use App\Service\Movie\FetchMoviesByGenreService;
use App\Service\Movie\MovieRecommendationService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MovieRecommendationServiceTest extends TestCase
{
    public function testGetRecommendationsRequestSuccess(): void
    {
        $fetchMoviesByGenreService = $this->createFetchMoviesBYGenreServiceMock();
        $cache = $this->createCacheInterfaceMock();
        $logger = $this->createLoggerInterfaceMock();
        $service = $this->createMovieRecommendationService($fetchMoviesByGenreService, $cache, $logger);

        $user = $this->createUserMock();
        $user
            ->method('getId')
            ->willReturn(1);
        $favoriteGenres = new ArrayCollection([
            $this->createGenreMock(1),
            $this->createGenreMock(2),
        ]);
        $user
            ->method('getFavoriteGenres')
            ->willReturn($favoriteGenres);

        $movieData1 = $this->createDummyMovieData(1, 'Movie 1');
        $movieData2 = $this->createDummyMovieData(2, 'Movie 2');
        $movieData3 = $this->createDummyMovieData(3, 'Movie 3');

        $fetchMoviesByGenreService
            ->method('execute')
            ->willReturn([$movieData1, $movieData2, $movieData3]);

        $cache
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                return $callback($this->createMock(ItemInterface::class));
            });

        $result = $service->getRecommendations($user, 2);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(MovieData::class, $result[0]);
        $this->assertInstanceOf(MovieData::class, $result[1]);
    }

    public function testGetRecommendationsWithEmptyFavoriteGenres(): void
    {
        $fetchMoviesByGenreService = $this->createFetchMoviesBYGenreServiceMock();
        $cache = $this->createCacheInterfaceMock();
        $logger = $this->createLoggerInterfaceMock();
        $service = $this->createMovieRecommendationService($fetchMoviesByGenreService, $cache, $logger);

        $user = $this->createUserMock();
        $user
            ->method('getId')
            ->willReturn(1);
        $user
            ->method('getFavoriteGenres')
            ->willReturn(new ArrayCollection());

        $cache
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                return $callback($this->createMock(ItemInterface::class));
            });

        $logger
            ->expects($this->once())
            ->method('info')
            ->with('User has no favorite genres.', ['user_id' => 1]);

        $result = $service->getRecommendations($user);

        $this->assertEmpty($result);
    }

    public function testGetRecommendationsWithRequestFailure(): void
    {
        $fetchMoviesByGenreService = $this->createFetchMoviesBYGenreServiceMock();
        $cache = $this->createCacheInterfaceMock();
        $logger = $this->createLoggerInterfaceMock();
        $service = $this->createMovieRecommendationService($fetchMoviesByGenreService, $cache, $logger);

        $user = $this->createUserMock();
        $user
            ->method('getId')
            ->willReturn(1);
        $favoriteGenres = new ArrayCollection([
            $this->createGenreMock(1),
            $this->createGenreMock(2),
        ]);
        $cache
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                return $callback($this->createMock(ItemInterface::class));
            });
        $user
            ->method('getFavoriteGenres')
            ->willReturn($favoriteGenres);

        $fetchMoviesByGenreService
            ->method('execute')
            ->willThrowException(new \Exception('Failed to fetch movies by genre.'));

        $logger
            ->method('error')
            ->with('Failed to fetch recommended movies.', [
                'user_id' => 1,
                'error' => 'Failed to fetch movies by genre.',
            ]);

        $result = $service->getRecommendations($user);

        $this->assertEmpty($result);
    }

    public function testGetRecommendationsWithInvalidMaxMovies(): void
    {
        $fetchMoviesByGenreService = $this->createFetchMoviesBYGenreServiceMock();
        $cache = $this->createCacheInterfaceMock();
        $logger = $this->createLoggerInterfaceMock();
        $service = $this->createMovieRecommendationService($fetchMoviesByGenreService, $cache, $logger);

        $user = $this->createUserMock();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Max movies must be greater than 0.');

        $service->getRecommendations($user, 0);
    }

    public function testGetRecommendationsWithCache(): void
    {
        $fetchMoviesByGenreService = $this->createFetchMoviesBYGenreServiceMock();
        $cache = $this->createCacheInterfaceMock();
        $logger = $this->createLoggerInterfaceMock();
        $service = $this->createMovieRecommendationService($fetchMoviesByGenreService, $cache, $logger);

        $user = $this->createUserMock();
        $user
            ->method('getId')
            ->willReturn(1);

        $favoriteGenres = new ArrayCollection([
            $this->createGenreMock(1),
            $this->createGenreMock(2),
        ]);

        $cache
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                $this->assertStringStartsWith(MovieRecommendationService::CACHE_KEY_BASE, $key);

                $item = $this->createMock(ItemInterface::class);
                $item
                    ->expects($this->once())
                    ->method('expiresAfter')
                    ->with(MovieRecommendationService::EXPIRATION_TIME);

                return $callback($item);
            });

        $user
            ->method('getFavoriteGenres')
            ->willReturn($favoriteGenres);

        $movieData1 = $this->createDummyMovieData(1, 'Movie 1');
        $movieData2 = $this->createDummyMovieData(2, 'Movie 2');

        $fetchMoviesByGenreService
            ->method('execute')
            ->willReturn([$movieData1, $movieData2]);

        $result = $service->getRecommendations($user);

        $this->assertCount(2, $result);
    }

    public function testGetRecommendationsWithDuplicateMovies(): void
    {
        $fetchMoviesByGenreService = $this->createFetchMoviesBYGenreServiceMock();
        $cache = $this->createCacheInterfaceMock();
        $logger = $this->createLoggerInterfaceMock();
        $service = $this->createMovieRecommendationService($fetchMoviesByGenreService, $cache, $logger);

        $user = $this->createUserMock();
        $user
            ->method('getId')
            ->willReturn(1);
        $favoriteGenres = new ArrayCollection([
            $this->createGenreMock(1),
            $this->createGenreMock(2),
        ]);
        $user
            ->method('getFavoriteGenres')
            ->willReturn($favoriteGenres);

        $movieData1 = $this->createDummyMovieData(1, 'Movie 1');
        $movieData2 = $this->createDummyMovieData(2, 'Movie 2');
        $movieData3 = $this->createDummyMovieData(1, 'Movie 1');

        $fetchMoviesByGenreService
            ->method('execute')
            ->willReturn([$movieData1, $movieData2, $movieData3]);

        $cache
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                return $callback($this->createMock(ItemInterface::class));
            });

        $result = $service->getRecommendations($user, 2);

        $this->assertCount(2, $result);
    }

    private function createFetchMoviesBYGenreServiceMock(): FetchMoviesByGenreService&MockObject
    {
        return $this->createMock(FetchMoviesByGenreService::class);
    }

    private function createCacheInterfaceMock(): CacheInterface&MockObject
    {
        return $this->createMock(CacheInterface::class);
    }

    private function createLoggerInterfaceMock(): LoggerInterface&MockObject
    {
        return $this->createMock(LoggerInterface::class);
    }

    private function createMovieRecommendationService(
        FetchMoviesByGenreService $service,
        CacheInterface $cache,
        LoggerInterface $logger,
    ): MovieRecommendationService {
        return new MovieRecommendationService($service, $cache, $logger);
    }

    private function createUserMock(): User&MockObject
    {
        return $this->createMock(User::class);
    }

    private function createGenreMock(int $tmdbId): Genre&MockObject
    {
        $genre = $this->createMock(Genre::class);
        $genre
            ->method('getTmdbId')
            ->willReturn($tmdbId);

        return $genre;
    }

    private function createDummyMovieData(int $id, string $title): MovieData
    {
        return new MovieData(
            id: $id,
            title: $title,
            posterPath: '/poster1.jpg',
            releaseDate: '2021-01-01',
            overview: 'Overview 1',
            adult: false,
            backdropPath: '/backdrop1.jpg',
            originalLanguage: 'en',
            originalTitle: 'Original Title 1',
            popularity: 87,
            voteAverage: 8.5,
            voteCount: 100,
            video: false,
            genreIds: [21, 54],
        );
    }
}
