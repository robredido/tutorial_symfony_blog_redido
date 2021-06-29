<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends AbstractController
{
//    /**
//     * @Route("/articles", name="article")
//     */
//    public function index(): Response
//    {
//        return $this->render('article/index.html.twig', [
//            'controller_name' => 'ArticleController',
//        ]);
//    }

    /**
     * @Route("/articles/create", name="create_article")
     */
    public function createArticle(): Response
    {
        // you can fetch the EntityManager via $this->getDoctrine()
        // or you can add an argument to the action: createProduct(EntityManagerInterface $entityManager)
        $entityManager = $this->getDoctrine()->getManager();

        $article = new Article();
        $article->setTitle('My favorite beach in Visayas');
        $article->setBody('cebu');

        // tell Doctrine you want to (eventually) save the Article (no queries yet)
        $entityManager->persist($article);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new article with id '.$article->getId());
    }

    /**
     * @Route("/articles/create/post", methods={"POST"}, name="create_article_post")
     * @param Request $request
     * @return Response
     */
    public function createArticlePost(Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $article = new Article();
        $article->setTitle($data['title']);
        $article->setBody($data['body']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return new Response('Inserted! ID: '.$article->getId());
    }

    /**
     * @Route("/articles/show/{id}", methods={"GET"}, name="show_article")
     * @param int $id
     * @return Response
     */
    public function showArticle(int $id): Response
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for id '.$id
            );
        }

        return new Response('Check out this great article: Title: "'.$article->getTitle().'" Body: "'.$article->getBody().'"');
    }

    /**
     * @Route("/articles/show/automatic/{id}", methods={"GET"}, name="show_article_automatically")
     * @param Article $article
     * @return Response
     */
    public function showArticleAutomatically(Article $article): Response
    {
        // Show article automatically and "Print all properties and values"
        return new Response(print_r($article, true));
    }

    /**
     * @Route("/articles/update/{id}", methods={"POST"}, name="update_article")
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function updateArticle(int $id, Request $request): Response
    {
        // get article from db
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for id '.$id
            );
        }

        // get new details for the article
        $body = $request->getContent();
        $data = json_decode($body, true);

        // set the new details
        $article->setTitle($data['title']);
        $article->setBody($data['body']);
        $entityManager->flush();

        return $this->redirectToRoute('show_article_automatically', [
            'id' => $article->getId()
        ]);
    }

    /**
     * @Route("/articles/{id}/comments", methods={"POST"}, name="add_article_comments")
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function addComments(int $id, Request $request): Response
    {
        // get article from db
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for id '.$id
            );
        }

        // get new comments for the article
        $body = $request->getContent();
        $data = json_decode($body, true);

        // set the new comments
        $article->setComments($data['comments']);
        $entityManager->flush();

        return $this->redirectToRoute('show_article_automatically', [
            'id' => $article->getId()
        ]);
    }

    /**
     * @Route("/articles", methods={"GET"}, name="search_by_title")
     * @param Request $request
     * @return Response
     */
    public function searchByTitle(Request $request): Response
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->searchByTitle($request->query->get('title'));

        return new Response(print_r($articles, true));
    }
}
