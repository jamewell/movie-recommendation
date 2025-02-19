<?php

namespace App\Service\Movie;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class TmdbApiService
{
    protected string $apiKey;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected ContainerBagInterface $params,
    ) {
        try {
            $this->apiKey = $this->params->get('tmdb_api_key');
        } catch (\Throwable $exception) {
            throw new \RuntimeException('TMDB API key is missing: '.$exception->getMessage());
        }
    }
}
