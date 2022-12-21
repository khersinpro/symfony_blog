<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileController extends AbstractController
{
    #[Route('/profile/{index?1}', name: 'app_profile', requirements: ['index' => '\d+'])]
    public function userProfile(ArticleRepository $articleRepository, int $index ): Response
    {
        $limit = 10;
        $offset = (($index < 1 ? 1 : $index) - 1) * $limit;
        $currentUserId = $this->getUser()->getId();
        $currentUserArticles = $articleRepository->findBy(['author' => $currentUserId], ['createdAt' => 'DESC'], $limit, $offset );
        $countOfAllCurrentUserArticles = $articleRepository->count(['author' => $currentUserId]);
        $numberOfPages = ceil($countOfAllCurrentUserArticles / $limit);

        return $this->render('profile/index.html.twig', [
            'articles' => $currentUserArticles,
            'numberOfPages' => $numberOfPages
        ]);
    }

    #[Route('/profile/editavatar', name: 'app_editProfilePicture')]
    public function editProfilePicture(Request $request, EntityManagerInterface $manager, Filesystem $fs, FileUploader $fileUploader): Response
    {
        $form = $this->createFormBuilder()
        ->add('update_profile_avatar', FileType::class, [
            'constraints' => [
                new Image([
                    'mimeTypes' => ['image/png','image/jpeg', 'image/webp'],
                    'mimeTypesMessage' => 'Seul les images au format PNG, JPG et WEBP sont accépté.',
                    'maxSize' => '2M',
                    'maxSizeMessage' => 'Le fichier {{ name }} est trop volumineux.'
                ])
            ]
        ])
        ->add('Envoyer', SubmitType::class, ['label' => 'Modifier'])
        ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newUserAvatar = $form->get('update_profile_avatar')->getData();
            if ($newUserAvatar) {
                $currentUser = $this->getUser();
                $currentUserAvatar = $currentUser->getAvatar();

                $newUserAvatarFilename = $fileUploader->uploadOneFile($newUserAvatar, $this->getParameter('profile.folder'));
                $currentUser->setAvatar($this->getParameter('profile.public.path').$newUserAvatarFilename);

                if ($currentUserAvatar !== $this->getParameter('default.profile.avatar')) {
                    $fs->remove($this->getParameter('profile.folder').pathinfo($currentUserAvatar, PATHINFO_BASENAME));
                }

                $manager->flush();
                $this->addFlash('success', 'Photo mise à jour.');
            } else {
                $this->addFlash('danger', 'Une erreur est survenue, veuillez réessayer plus tard.');
            }
            
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit_profile_picture.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/profile/updatepassword', name: 'app_updatePassword')]
    public function updateUserPassword(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, 
    EntityManagerInterface $manager): Response
    {
        $constraints =  [
            new Length([
                'min' => 6,
                'minMessage' => 'Le mot de passe doit faire 6 caractères minimun'
            ]),
            new NotBlank([
                'message' => 'Veuillez renseigner un mot de passe'
            ])
        ];
        $form = $this->createFormBuilder()
            ->add('new_password', PasswordType::class, ['label' => 'Nouveau mot de passe :', 'constraints' => $constraints])
            ->add('confirm_password', PasswordType::class, ['label' => 'Confirmation du nouveau mot de passe :', 'constraints' => $constraints])
            ->add('submit', SubmitType::class, ['label' => 'Valider'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if ($newPassword && $confirmPassword && $newPassword === $confirmPassword) {
                $currentUser = $this->getUser();
                $currentUser->setPassword($userPasswordHasherInterface->hashPassword($currentUser, $newPassword));
                $manager->flush();
                $this->addFlash('success', 'Mot de passe modifier avec succés.');
            } else {
                $this->addFlash('danger','Mot de passe non identique, veuillez réessayer.');
            }
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit_user_password.html.twig', [
            'form' => $form,
        ]);
    }
}
