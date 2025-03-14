<?php

namespace App\Controller\Movie;

use App\Entity\User;
use App\Service\Movie\MovieRecommendationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/movie/recommendation')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class MovieRecommendationController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/', name: 'app_movie_recommendation')]
    public function index(MovieRecommendationService $movieRecommendationService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $recommendedMovies = $movieRecommendationService->getRecommendations($user);

            return $this->render('movie_recommendation/index.html.twig', [
                'movies' => $recommendedMovies,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to fetch movie recommendations.', [
                'exception' => $e->getMessage(),
            ]);

            return $this->render('movie_recommendation/index.html.twig', [
                'error' => 'An error occurred while fetching movie recommendations.',
                'movies' => [],
            ]);
        }
    }
}
