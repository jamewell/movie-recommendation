<?php

namespace App\Service\Movie;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
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
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected ContainerBagInterface $params,
    ) {
        parent::__construct($httpClient, $params);
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
        $response = $this->httpClient->request('GET', 'https://api.themoviedb.org/3/genre/movie/list', [
            'query' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new HttpException($response->getStatusCode(), 'Failed to fetch genres.');
        }

        return $response->toArray()['genres'] ?? null;
    }
}
