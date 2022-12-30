<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/blog/user/createarticle/', name: 'app_create_article')]
    public function createArticle(Request $request, FileUploader $fileUploader, ArticleRepository $articleRepository): Response
    {
        $article = new Article(); 
        $form = $this->createForm(ArticleType::class, $article, [
                "category" => $this->getParameter('article.category.list')
        ]);

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
                $articleRepository->save($article, true);  
    
                $this->addFlash('success', 'Votre article a été créer avec succés.');
                return $this->redirectToRoute('app_blog');
            } else {
                $this->addFlash('danger', 'L\'image fournit est invalide.');
            }
        }

        return $this->render('blog/articles/article_add.html.twig', [ 'form' => $form ]);
    }

    
    #[Route('/blog/user/modify/article/{id<\d+>}', name: 'app_modify_article')]
    public function modifyArticle(Article $article, Request $request, EntityManagerInterface $manager, FileUploader $fileUploader, Filesystem $fs)
    {   
        $currentUser = $this->getUser();
        if ($currentUser !== $article->getAuthor()) {
            return $this->redirectToRoute('app_blog');
        } 
        
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentArticleImg = $article->getImage();
            $newArticleImg = $form->get('image')->getData();

            if ($newArticleImg) {
                $newArticleImgFilename = $fileUploader->uploadOneFile($newArticleImg, $this->getParameter('article.image.folder'));
                $article->setImage($this->getParameter('article.image.public_path').$newArticleImgFilename);
                $fs->remove($this->getParameter('article.image.folder').pathinfo($currentArticleImg, PATHINFO_BASENAME));
            }
            $manager->flush();
            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/articles/article_edit.html.twig', [ 'form' => $form ]);
    }

    #[Route('/blog/user/article/delete/{id<\d+>}', name: 'app_delete_article', methods: ['POST'])]
    public function deleteArticle(ArticleRepository $articleRepository, Article $article, Filesystem $fs,Request $request)
    {
        $currentUser = $this->getUser();
        $submittedCsrfToken = $request->request->get('token');
        
        if (!$article || $article->getAuthor() !== $currentUser || !$this->isCsrfTokenValid('delete-item', $submittedCsrfToken)) {
            return $this->redirectToRoute('app_blog');
        }

        $fs->remove($this->getParameter('article.image.folder').pathinfo($article->getImage(), PATHINFO_BASENAME));
        $articleRepository->remove($article, true);  
        
        return $this->redirectToRoute('app_profile');
    }
}

