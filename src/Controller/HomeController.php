<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/{page<\d+>?1}/{category?}', name: 'app_blog')]
    public function displayArticles(ArticleRepository $articleRepository, int $page, ?string $category): Response
    {
        $allCategory = $this->getParameter('article.category.list');
        if ($category && !in_array($category, $allCategory)) {
            return  $this->redirectToRoute('app_blog');
        }

        $limit = 12;
        $offset = ($page -1) * $limit ;
        $nbrOfPages = ceil($articleRepository->count($category ? ["category" => $category] : []) / $limit);
        $articles = $articleRepository->getArticlesWithAuthor($offset, $limit, $category);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'nbrOfPages' => $nbrOfPages,
            'actualPage' => $page,
            'category' => $allCategory,
            'actualCategory' => $category?? null
        ]);
    }

    #[Route('/blog/article/{id}/{page?1}', name: 'app_show_article', requirements: ['id' => '\d+', 'page' => '\d+'], methods: ['POST', 'GET'])]
    public function showArticle(ArticleRepository $articleRepository, int $id, Request $request, CommentRepository $commentRepository, int $page)
    {
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $nbrOfPages = ceil($commentRepository->count(['article' => $id]) / $limit );

        $articleComments = $commentRepository->findBy(['article' => $id], ['createdAt' => ' DESC'], $limit, $offset);
        $article = $articleRepository->findOneBy(['id' => $id]);
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment)
            ->add('submit', SubmitType::class, ['label' => 'Envoyer']);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setArticle($article);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setAuthor($this->getUser());
            $commentRepository->save($comment, true);
            return $this->redirect($request->getUri());
        }

        return $this->render('blog/articles/show_article.html.twig', [
            'article' => $article,
            'form' => $commentForm,
            'comments' => $articleComments,
            'nbrOfPages' => $nbrOfPages,
            'articleId' => $id
        ]);
    }
}
