<?php
/**
 * Created by IntelliJ IDEA.
 * User: N-138
 * Date: 05/07/2021
 * Time: 4:37 PM
 */

namespace App\Controller\Rest;

use App\Entity\Article;
use App\Form\ArticleType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ArticleController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/articles")
     * @param Request $request
     * @return View
     */
    public function getArticles(Request $request)
    {
        $title = $request->get('title');
        $entityManager = $this->getDoctrine()->getManager();

        if (!$title) {
            $articles = $entityManager->getRepository(Article::class)->findAll();
        } else {
            $articles = $this->getDoctrine()
                ->getManager()
                ->getRepository(Article::class)
                ->searchByTitle($title);
        }

        return View::create($articles, Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/articles/{id}")
     * @ParamConverter("article")
     * @param Article $article
     * @return View
     */
    public function getArticleById(Article $article)
    {
        return View::create($article, Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/articles")
     * @param Request $request
     * @return View
     */
    public function postArticle(Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

//        var_dump($data);
//        var_dump($request->request->all());

        $form = $this->createForm(ArticleType::class, new Article());
        $form->submit($data);

        if (!$form->isValid()) {
            return $this->view('Article is invalid', Response::HTTP_NOT_FOUND);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($form->getData());
        $entityManager->flush();

        return $this->view('New article is inserted', Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/articles/{id}")
     * @param Article $article
     * @param Request $request
     * @return View
     * @ParamConverter("article")
     */
    public function putArticle(Article $article, Request $request)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if ($article) {
            $article->setTitle($data['title']);
            $article->setBody($data['body']);
        } else {
            return $this->view('Article was not found.', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if (!$form->isValid()) {
            return $this->view('Article is invalid', Response::HTTP_NOT_FOUND);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        return View::create($article, Response::HTTP_OK);
    }
}
