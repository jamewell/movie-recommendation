<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\User\ProfileEditFormType;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use League\Flysystem\FilesystemException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $this->getUser(),
        ]);
    }

    #[Route('profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FileUploader $fileUploader): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $firstName = $form->get('firstName')->getData();
            $lastName = $form->get('lastName')->getData();
            $profilePicture = $form->get('profilePicture')->getData();
            $favoriteGenres = $form->get('favoriteGenres')->getData();

            if ($profilePicture) {
                try {
                    $pictureFilename = $fileUploader->uploadProfilePicture($profilePicture, (string) $user->getId());
                } catch (FilesystemException $e) {
                    throw new RuntimeException('Follow error occurred uploading image: '.$e->getMessage());
                }
                $user->setProfilePicture($pictureFilename);
            }

            if ($favoriteGenres) {
                foreach ($favoriteGenres as $genre) {
                    $user->addFavoriteGenres($genre);
                }
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);

            $this->userRepository->updateUser($user);

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'editForm' => $form,
        ]);
    }
}
