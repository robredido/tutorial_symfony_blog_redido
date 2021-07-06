<?php
/**
 * Created by IntelliJ IDEA.
 * User: N-138
 * Date: 06/07/2021
 * Time: 4:08 PM
 */

namespace App\Controller\Rest;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends AbstractFOSRestController
{
    /**
     * @Rest\Post("/articles/{id}/comments")
     * @param Article $article
     * @param Request $request
     * @return View
     * @ParamConverter("article")
     */
    public function postCommentAction(Article $article, Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setBody($data['body']);

        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->submit($data);

        if(!$commentForm->isValid()){
            return $this->view('Comment is invalid', Response::HTTP_NOT_FOUND);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->view('New comment is inserted', Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/articles/{id}/comments")
     * @param Article $article
     * @ParamConverter("article")
     * @return View
     */
    public function getCommentAction(Article $article)
    {
        return View::create($article->getComments(), Response::HTTP_OK);
    }
}
