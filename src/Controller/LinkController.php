<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class LinkController extends AbstractController
{
    #[Route('/r/{dodatek}', name: 'app_link_dodatek', methods: ['GET'])]
    public function redirection($dodatek, Request $request, LinkRepository $linkRepository): Response
    {
        $link_item = $linkRepository->findByLink($dodatek);
        $url = $link_item->getFullLink();
        return $this->redirect($url);
    }


    #[Route('/link', name: 'app_link_index', methods: ['GET'])]
    public function index(LinkRepository $linkRepository): Response
    {
        return $this->render('link/index.html.twig', [
            'links' => $linkRepository->findAll(),
        ]);
    }

    #[Route('/link/new', name: 'app_link_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LinkRepository $linkRepository): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $link->setCreatedAt(new \DateTime());
            $link->setUpdatedAt(new \DateTime());
            $link->setUsesCount(0);
            $link->setIsActive(true);
            if($link->getLink()==null){
                $link->setLink(hash('crc32', $link->getFullLink()));
            }

            $linkRepository->save($link, true);

            return $this->redirectToRoute('app_link_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('link/new.html.twig', [
            'link' => $link,
            'form' => $form,
        ]);
    }

    #[Route('/link/{id}', name: 'app_link_show', methods: ['GET'])]
    public function show(Link $link): Response
    {
        return $this->render('link/show.html.twig', [
            'link' => $link,
        ]);
    }

    #[Route('/link/{id}/edit', name: 'app_link_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Link $link, LinkRepository $linkRepository): Response
    {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $linkRepository->save($link, true);

            return $this->redirectToRoute('app_link_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('link/edit.html.twig', [
            'link' => $link,
            'form' => $form,
        ]);
    }

    #[Route('/link/{id}', name: 'app_link_delete', methods: ['POST'])]
    public function delete(Request $request, Link $link, LinkRepository $linkRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$link->getId(), $request->request->get('_token'))) {
            $linkRepository->remove($link, true);
        }

        return $this->redirectToRoute('app_link_index', [], Response::HTTP_SEE_OTHER);
    }
}
