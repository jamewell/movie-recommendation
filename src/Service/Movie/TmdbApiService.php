<?php

namespace App\Service\Movie;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class TmdbApiService
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected string $apiKey,
        protected LoggerInterface $logger,
        protected string $baseUrl = 'https://api.themoviedb.org/3/',
    ) {
        if (empty($apiKey)) {
            throw new \RuntimeException('TMDB API key is empty or not configured.');
        }
    }

    protected function getApiUrl(string $endpoint): string
    {
        return $this->baseUrl.$endpoint;
    }
}
