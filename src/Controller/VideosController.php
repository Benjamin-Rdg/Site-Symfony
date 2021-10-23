<?php

namespace App\Controller;

use App\Entity\Videos;
use App\Form\VideosType;
use App\Repository\VideosRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/videos')]
class VideosController extends AbstractController
{
    #[Route('/', name: 'videos_index', methods: ['GET'])]
    public function index(VideosRepository $videosRepository): Response
    {
        return $this->render('videos/index.html.twig', [
            'videos' => $videosRepository->findAll(),
        ]);
    }
    #[isGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'videos_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $video = new Videos();
        $form = $this->createForm(VideosType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($video);
            $entityManager->flush();

            return $this->redirectToRoute('videos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('videos/new.html.twig', [
            'video' => $video,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'videos_show', methods: ['GET'])]
    public function show(Videos $video): Response
    {
        return $this->render('videos/show.html.twig', [
            'video' => $video,
        ]);
    }
    #[isGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'videos_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Videos $video): Response
    {
        $form = $this->createForm(VideosType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('videos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('videos/edit.html.twig', [
            'video' => $video,
            'form' => $form,
        ]);
    }
    #[isGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'videos_delete', methods: ['POST'])]
    public function delete(Request $request, Videos $video): Response
    {
        if ($this->isCsrfTokenValid('delete'.$video->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($video);
            $entityManager->flush();
        }

        return $this->redirectToRoute('videos_index', [], Response::HTTP_SEE_OTHER);
    }
}
