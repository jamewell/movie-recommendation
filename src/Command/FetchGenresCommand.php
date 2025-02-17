<?php

namespace App\Command;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use App\Service\TmdbApiService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-genres',
    description: 'Fetches movie genres from TMDB and saves them into database.',
)]
class FetchGenresCommand extends Command
{
    public function __construct(
        private readonly TmdbApiService $tmdbApiService,
        private readonly GenreRepository $genreRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching genres...');

        try {
            $genres = $this->tmdbApiService->fetchGenres();

            if (!$genres) {
                $output->writeln('<error>No genres found.</error>');

                return Command::FAILURE;
            }

            foreach ($genres as $genre) {
                $existingGenre = $this->genreRepository->findByName($genre['name']);

                if ($existingGenre) {
                    $output->writeln("<info>$genre[name] already exists.</info>");
                    continue;
                }

                $newGenre = new Genre();
                $newGenre->setName($genre['name']);
                $newGenre->setTmdbId($genre['id']);

                $this->genreRepository->store($newGenre);
                $output->writeln("<info>$genre[name] created.</info>");
            }
        } catch (\Throwable $exception) {
            $output->writeln('<error>'.$exception->getMessage().'</error>');
        }

        return Command::SUCCESS;
    }
}
