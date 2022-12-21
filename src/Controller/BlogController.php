<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog')]
    public function displayArticles(ArticleRepository $articleManager, UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();
        $userArticles = $currentUser;
        $articles = $articleManager->getArticlesWithAuthor();
        // foreach ($userArticles as $article) {
        //     dump($article);
        // }
        $userTest = $articleManager->findBy(['author' => $currentUser->getId()]);
        dump($userTest);
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'test' => $userArticles
        ]);
    }

    #[Route('/blog/createarticle/', name: 'app_create_article')]
    public function createArticle(Request $request, EntityManagerInterface $manager, FileUploader $fileUploader): Response
    {
        $article = new Article(); 
        $form = $this->createForm(ArticleType::class, $article)
            ->add('submit', SubmitType::class, ['label' => 'Créer l\'article']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser = $this->getUser();
            $articleImageToUpload = $form->get('image')->getData();
            if ($articleImageToUpload) {
                $articleImageFilename = $fileUploader->uploadOneFile($articleImageToUpload, $this->getParameter('article.image.folder'));

                $article->setAuthor($currentUser)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setImage($this->getParameter('article.image.public_path').$articleImageFilename)
                    ->setLikes(0);
                $manager->persist($article);
                $manager->flush();    
    
                $this->addFlash('success', 'Votre article a été créer avec succés.');
                return $this->redirectToRoute('app_blog');
            } else {
                $this->addFlash('danger', 'L\'image fournit est invalide.');
            }
        }

        return $this->render('blog/articles/article_form.html.twig', [
            'form' => $form,
        ]);
    }
}
