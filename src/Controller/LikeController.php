<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Like;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{
    #[Route('/blog/user/article/like/{id}', name: 'app_like_article', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function likeArticle(Article $article, Request $request, LikeRepository $likeRepository, EntityManagerInterface $manager)
    {
        $currentUser = $this->getUser();
        $articleIsLiked = $likeRepository->findOneBy(['author' => $currentUser, 'article' => $article]);

        if (!$articleIsLiked) {
            $newLike = new Like();
            $newLike->setArticle($article);
            $newLike->setAuthor($currentUser);
            $article->setLikes($article->getLikes() + 1);
            $manager->persist($newLike);
            $manager->flush();
        } else {
            $article->setLikes($article->getLikes() - 1);
            $manager->remove($articleIsLiked);
            $manager->flush();
        }
        $referer = $request->server->get('HTTP_REFERER');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_blog');
    }
}
