<?php

namespace App\Controller;

use App\Entity\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ArticleController extends AbstractController
{
    /**
     * @Route(path="/", methods={"GET"}, name="article_list")
     */
    public function index(): Response
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        return $this->render('articles/index.html.twig', array('articles' => $articles));
    }

    /**
     * @Route(path="/article/save", methods={"POST"})
     */
    public function save(): Response
    {
        $article = new Article();
        $article->setTitle('Article two');
        $article->setBody('This is the body for article two');

        $this->accessDatabase($article, "save");

        return new Response('Article saved with id ' . $article->getId());
    }

    /**
     * @Route("/article/new", methods={"GET","POST"}, name="new_article")
     */
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class,
                array('attr' => array('class' => 'form-control')))
            ->add('body', TextareaType::class,
                array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('save', SubmitType::class,
                array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary mt-3')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $this->accessDatabase($article, 'save');
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/article/edit/{id}", methods={"GET","POST"}, name="edit_article")
     */
    public function edit(Request $request, $id): Response
    {
        $article = $this->getArticleById($id);

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class,
                array('attr' => array('class' => 'form-control')))
            ->add('body', TextareaType::class,
                array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('save', SubmitType::class,
                array('label' => 'Update', 'attr' => array('class' => 'btn btn-primary mt-3')))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accessDatabase($article, 'edit');
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/article/{id}", methods={"GET"}, name="show_article")
     */
    public function show($id): Response
    {
        $article = $this->getArticleById($id);
        return $this->render('articles/show.html.twig', array('article' => $article));
    }

    /**
     * @Route(path="/article/delete/{id}", methods={"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        $article = $this->getArticleById($id);
        $this->accessDatabase($article, 'delete');
        $response = new Response();
        $response->send();
    }


    // Utility functions
    private function getArticleById($id): ?object
    {
        return $this->getDoctrine()->getRepository(Article::class)->find($id);
    }

    private function accessDatabase($article, $action)
    {
        $entityManager = $this->getDoctrine()->getManager();
        switch ($action) {
            case "save":
                $entityManager->persist($article);
                break;
            case "delete":
                $entityManager->remove($article);
                break;
            case "edit":
            default:
                break;

        }
        $entityManager->flush();
    }
}