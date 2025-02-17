<?php

namespace App\Service;

use http\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    private string $apiKey;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ContainerBagInterface $params,
    ) {
        try {
            $this->apiKey = $this->params->get('tmdb_api_key');
        } catch (\Throwable $exception) {
            throw new RuntimeException('TMDB API key is missing: '.$exception->getMessage());
        }
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

        return $response->toArray()['genres'] ?? null;
    }
}
