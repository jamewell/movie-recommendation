<?php

namespace App\Service\Movie;

use App\Data\Movie\MovieData;
use App\Entity\User;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MovieRecommendationService
{
    public const CACHE_KEY_BASE = 'movie_recommendation_user_';

    public function __construct(
        private readonly FetchMoviesByGenreService $service,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<MovieData>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function getRecommendations(User $user, int $maxMovies = 10): array
    {
        if ($maxMovies < 1) {
            throw new \InvalidArgumentException('Max movies must be greater than 0.');
        }

        $cacheKey = self::CACHE_KEY_BASE.$user->getId();

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($user, $maxMovies) {
            $item->expiresAfter(3600 * 6);

            $favoriteGenres = $user->getFavoriteGenres();
            if (0 === count($favoriteGenres)) {
                return [];
            }

            $recommendedMovies = [];
            foreach ($favoriteGenres as $genre) {
                array_push($recommendedMovies, ...$this->service->execute([$genre->getTmdbId()]));
            }

            $uniqueMovies = [];
            foreach ($recommendedMovies as $movie) {
                $uniqueMovies[$movie->getId()] = $movie;
            }
            $uniqueMovies = array_values($uniqueMovies);

            return array_slice($uniqueMovies, 0, $maxMovies);
        });
    }

    public function clearCache(User $user): void
    {
        try {
            $this->cache->delete(self::CACHE_KEY_BASE.$user->getId());
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Failed to clear cache for user.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
