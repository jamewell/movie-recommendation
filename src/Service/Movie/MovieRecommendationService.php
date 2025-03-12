<?php

namespace App\Service\Movie;

use App\Data\Movie\MovieData;
use App\Entity\User;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MovieRecommendationService
{
    public function __construct(
        private readonly FetchMoviesByGenreService $service,
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
     */
    public function execute(User $user, int $maxMovies = 10): array
    {
        if ($maxMovies < 1) {
            throw new \InvalidArgumentException('Max movies must be greater than 0.');
        }

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
    }
}
