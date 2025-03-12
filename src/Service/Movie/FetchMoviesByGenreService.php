<?php

namespace App\Service\Movie;

use App\Data\Movie\MovieData;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchMoviesByGenreService extends TmdbApiService
{
    private const DISCOVER_MOVIE_ENDPOINT = 'discover/movie';

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected string $apiKey,
    ) {
        parent::__construct($httpClient, $apiKey, $logger);
    }

    /**
     * @param array<int> $genreIds
     *
     * @return array<MovieData>
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function execute(array $genreIds, int $page = 1): array
    {
        if (empty($genreIds)) {
            throw new \InvalidArgumentException('At least one genre ID must be provided.');
        }

        if ($page < 1) {
            throw new \InvalidArgumentException('Page must be a positive integer.');
        }

        try {
            $response = $this->httpClient->request('GET', $this->getApiUrl(self::DISCOVER_MOVIE_ENDPOINT), [
                'query' => [
                    'api_key' => $this->apiKey,
                    'with_genres' => implode('|', $genreIds),
                    'page' => $page,
                ],
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $this->logger->error('Failed to fetch movies by genre.', [
                    'status_code' => $response->getStatusCode(),
                    'genre_ids' => $genreIds,
                ]);
                throw new HttpException($response->getStatusCode(), 'Failed to fetch movies by genre.');
            }

            $data = $response->toArray();
            if (!isset($data['results'])) {
                throw new \RuntimeException('Invalid API response: missing "results" key.');
            }

            $movieData = $data['results'];

            return array_map(fn (array $data) => MovieData::fromArray($data), $movieData);
        } catch (\Exception $e) {
            $this->logger->error('An error occurred while fetching movies by genre.', [
                'exception' => $e,
                'genre_ids' => $genreIds,
            ]);
            throw $e;
        }
    }
}
