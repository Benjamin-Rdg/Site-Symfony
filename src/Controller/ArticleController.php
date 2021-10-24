<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Tag;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/article')]
class ArticleController extends AbstractController
{

    #[Route('/', name: 'article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/news', name: 'article_new', methods: ['GET','POST'])]
    public function new(Request $request, SluggerInterface $slugger,TagRepository $tagRespository): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        $thumbnailFile = $form->get('thumbnail')->getData();

        if($thumbnailFile) {
            $uploadName = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($uploadName);
            $storedName = $safeFilename.'-'.uniqid().'.'.$thumbnailFile->guessExtension();

            try {
                $thumbnailFile->move(
                    $this->getParameter('thumbnails_directory'),
                    $storedName
                );
            } catch(FileException $e) {
                // handle exception ...
            }

            // update : store the filename instead of its contents
            $article->setThumbnail($storedName);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $tags = $form->get('tags')->getData();
            $tags = explode(',', $tags);

            foreach ($tags as $tag) {
                $tag = trim($tag);
                $entityTag = $tagRespository->findBy(['nom'=>$tag]);
                if (!isset($entityTag[0])){
                    $entityTag[0] = new Tag();
                    $entityTag[0] ->setNom($tag);
                    $entityManager->persist($entityTag[0]);
                }
                $article->addTag($entityTag[0]);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'article_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Article $article,TagRepository $tagRespository): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $tags = $form->get('tags')->getData();
            $tags = explode(',', $tags);

            foreach ($tags as $tag) {
                $tag = trim($tag);
                $entityTag = $tagRespository->findBy(['nom'=>$tag]);
                if (!isset($entityTag[0])){
                    $entityTag[0] = new Tag();
                    $entityTag[0] ->setNom($tag);
                    $entityManager->persist($entityTag[0]);
                }
                $article->addTag($entityTag[0]);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index', [], Response::HTTP_SEE_OTHER);
    }
}
