<?php

namespace App\Controller;

use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function userProfile(): Response
    {

        return $this->render('profile/index.html.twig', [

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
}
