<?php

namespace App\Data\Movie;

class MovieData
{
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly ?string $posterPath = null,
        private readonly ?string $releaseDate = null,
        private readonly ?string $overview = null,
        private readonly bool $adult = false,
        private readonly ?string $backdropPath = null,
        private readonly string $originalLanguage = 'en',
        private readonly string $originalTitle = '',
        private readonly int $popularity = 0,
        private readonly float $voteAverage = 0.0,
        private readonly int $voteCount = 0,
        private readonly bool $video = false,
        private readonly array $genreIds = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['id'], $data['title'])) {
            throw new \InvalidArgumentException('Invalid data array: missing required keys.');
        }

        return new self(
            $data['id'],
            $data['title'],
            $data['poster_path'],
            $data['release_date'],
            $data['overview'],
            $data['adult'],
            $data['backdrop_path'],
            $data['original_language'],
            $data['original_title'],
            $data['popularity'],
            $data['vote_average'],
            $data['vote_count'],
            $data['video'],
            $data['genre_ids'],
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function getReleaseDate(): ?string
    {
        return $this->releaseDate;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function isAdult(): bool
    {
        return $this->adult;
    }

    public function getBackdropPath(): ?string
    {
        return $this->backdropPath;
    }

    public function getOriginalLanguage(): string
    {
        return $this->originalLanguage;
    }

    public function getOriginalTitle(): string
    {
        return $this->originalTitle;
    }

    public function getPopularity(): int
    {
        return $this->popularity;
    }

    public function getVoteAverage(): float
    {
        return $this->voteAverage;
    }

    public function getVoteCount(): int
    {
        return $this->voteCount;
    }

    public function isVideo(): bool
    {
        return $this->video;
    }

    public function getGenreIds(): array
    {
        return $this->genreIds;
    }
}
