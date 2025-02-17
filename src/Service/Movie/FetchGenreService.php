<?php

namespace App\Service\Movie;

use http\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchGenreService extends TmdbApiService
{
    public function __construct(
        protected readonly HttpClientInterface $httpClient,
        protected readonly ContainerBagInterface $params,
    ) {
        parent::__construct($httpClient, $params);
    }

    /**
     * @return ?array<string,array<string,mixed>>
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchGenres(): ?array
    {
        $response = $this->httpClient->request('GET', 'https://api.themoviedb.org/3/genre/movie/list', [
            'query' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new RuntimeException('Failed to fetch genres. Status code: '.$response->getStatusCode());
        }

        if (!isset($response->toArray()['genres'])) {
            throw new RuntimeException('Failed to fetch genres. Invalid response.');
        }

        return $response->toArray()['genres'] ?? null;
    }
}
