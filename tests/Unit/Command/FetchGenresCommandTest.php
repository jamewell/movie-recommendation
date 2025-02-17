<?php

namespace App\Tests\Unit\Command;

use App\Command\FetchGenresCommand;
use App\Entity\Genre;
use App\Repository\GenreRepository;
use App\Service\TmdbApiService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FetchGenresCommandTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $tmdbApiService = $this->createTmdbApiServiceMock();
        $genreRepository = $this->createGenreRepositoryMock();
        $commandTester = $this->createCommandTester($tmdbApiService, $genreRepository);

        $tmdbApiService
            ->expects($this->once())
            ->method('fetchGenres')
            ->willReturn([
                ['id' => 1, 'name' => 'Action'],
                ['id' => 2, 'name' => 'Comedy'],
            ]);

        $genreRepository
            ->expects($this->exactly(2))
            ->method('findByName')
            ->withConsecutive(['Action'], ['Comedy'])
            ->willReturnOnConsecutiveCalls(null, null);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Fetching genres...', $output);
    }

    public function testExecuteNoGenres(): void
    {
        $tmdbApiService = $this->createTmdbApiServiceMock();
        $genreRepository = $this->createGenreRepositoryMock();
        $commandTester = $this->createCommandTester($tmdbApiService, $genreRepository);

        $tmdbApiService
            ->expects($this->once())
            ->method('fetchGenres')
            ->willReturn(null);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Fetching genres...', $output);
        $this->assertStringContainsString('No genres found.', $output);
    }

    public function testExecuteGenreExists(): void
    {
        $tmdbApiService = $this->createTmdbApiServiceMock();
        $genreRepository = $this->createGenreRepositoryMock();
        $commandTester = $this->createCommandTester($tmdbApiService, $genreRepository);

        $tmdbApiService
            ->expects($this->once())
            ->method('fetchGenres')
            ->willReturn([
                ['id' => 1, 'name' => 'Action'],
            ]);

        $genreRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Action')
            ->willReturn(new Genre());

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Fetching genres...', $output);
        $this->assertStringContainsString('Action already exists.', $output);
    }

    public function testExecuteInvalidGenreData(): void
    {
        $tmdbApiService = $this->createTmdbApiServiceMock();
        $genreRepository = $this->createGenreRepositoryMock();
        $commandTester = $this->createCommandTester($tmdbApiService, $genreRepository);

        $tmdbApiService
            ->expects($this->once())
            ->method('fetchGenres')
            ->willReturn([
                ['id' => 1],
                ['name' => 'Action'],
            ]);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Fetching genres...', $output);
        $this->assertStringContainsString('Invalid genre data.', $output);
    }

    public function testExecuteException(): void
    {
        $tmdbApiService = $this->createTmdbApiServiceMock();
        $genreRepository = $this->createGenreRepositoryMock();
        $commandTester = $this->createCommandTester($tmdbApiService, $genreRepository);

        $tmdbApiService
            ->expects($this->once())
            ->method('fetchGenres')
            ->willThrowException(new \Exception('An error occurred.'));

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Fetching genres...', $output);
        $this->assertStringContainsString('An error occurred.', $output);
    }

    private function createTmdbApiServiceMock(): TmdbApiService&MockObject
    {
        return $this->createMock(TmdbApiService::class);
    }

    private function createGenreRepositoryMock(): GenreRepository&MockObject
    {
        return $this->createMock(GenreRepository::class);
    }

    private function createCommandTester(TmdbApiService $tmdbApiService, GenreRepository $genreRepository): CommandTester
    {
        $application = new Application();
        $application->add(new FetchGenresCommand($tmdbApiService, $genreRepository));

        $command = $application->find('app:fetch-genres');

        return new CommandTester($command);
    }
}
