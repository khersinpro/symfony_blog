<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/blog/user/comment/delete/{id}', name: 'app_delete_comment', requirements : ['id' => '\d+'], methods: ['POST'])]
    public function deleteComment(Comment $comment, Request $request, CommentRepository $commentRepository)
    {
        $submittedCsrfToken = $request->request->get('token');
        if (!$comment || !$this->isCsrfTokenValid('delete-item', $submittedCsrfToken) || $comment->getAuthor() !== $this->getUser()) {
            return $this->redirectToRoute('app_blog');
        }

        $commentRepository->remove($comment, true);
        $this->addFlash('success', 'Commentaire supprimé avec succés.');

        return $this->redirect($request->server->get('HTTP_REFERER')?? '/');
    }
}
