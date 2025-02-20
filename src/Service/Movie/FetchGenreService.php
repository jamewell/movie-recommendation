<?php

namespace App\Service\Movie;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchGenreService extends TmdbApiService
{
    private const GENRE_MOVIE_LIST_ENDPOINT = 'genre/movie/list';

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected string $apiKey,
        protected LoggerInterface $logger,
    ) {
        parent::__construct($httpClient, $apiKey, $logger);
    }

    /**
     * @return ?array<int,array<string,mixed>>
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function execute(): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $this->getApiUrl(self::GENRE_MOVIE_LIST_ENDPOINT), [
                'query' => [
                    'api_key' => $this->apiKey,
                ],
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $this->logger->error('Failed to fetch genres.', [
                    'status_code' => $response->getStatusCode(),
                ]);
                throw new HttpException($response->getStatusCode(), 'Failed to fetch genres.');
            }

            $data = $response->toArray();
            if (!isset($data['genres'])) {
                $this->logger->warning('Invalid API response: missing "genres" key.', [
                    'response' => $data,
                ]);

                return null;
            }

            return $data['genres'];
        } catch (\Exception $e) {
            $this->logger->error('An error occurred while fetching genres.', [
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
