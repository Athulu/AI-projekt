<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/')]
class LinkController extends AbstractController
{
    #[Route('/r/{postfix}', name: 'app_link_postfix', methods: ['GET'])]
    public function redirection($postfix, Request $request, LinkRepository $linkRepository): Response
    {
        $link_item = $linkRepository->findByLink($postfix);
        $link_item->setLastUsedAt(new DateTime());
        $link_item->setUsesCount($link_item->getUsesCount() + 1);
        $linkRepository->save($link_item, true);

        $url = $link_item->getFullLink();
        return $this->redirect($url);
    }

    #[Route('/link/userlinks', name: 'app_link_index_for_user', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', 'ROLE_USER')]
    public function indexForUser(LinkRepository $linkRepository, UserRepository $userRepository): Response
    {
        return $this->render('link/index.html.twig', [
            'links' => $linkRepository->findByUser($this->getUser())
        ]);
    }

    #[Route('/link', name: 'app_link_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', 'ROLE_USER')]
    public function index(LinkRepository $linkRepository): Response
    {
        return $this->render('link/index.html.twig', [
            'links' => $linkRepository->findAll(),
        ]);
    }

    #[Route('/link/new', name: 'app_link_new', methods: ['GET', 'POST'])]
    public function new (Request $request, LinkRepository $linkRepository): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $link->setCreatedAt(new \DateTime());
            $link->setUpdatedAt(new \DateTime());
            $link->setUsesCount(0);
            $link->setIsActive(true);
            $link->setUser($this->getUser());
            if ($link->getLink() == null) {
                $link->setLink(hash('crc32', $link->getFullLink()));
            }

            $linkRepository->save($link, true);

            $routeName = $request->attributes->get('_route');
            if ($routeName == 'app_link_index') {
                return $this->redirectToRoute('app_link_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_link_index_for_user', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('link/new.html.twig', [
            'link' => $link,
            'form' => $form,
        ]);
    }



    #[Route('/link/{id}', name: 'app_link_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', 'ROLE_USER')]
    public function show(Link $link): Response
    {
        return $this->render('link/show.html.twig', [
            'link' => $link,
        ]);
    }

    #[Route('/link/{id}/edit', name: 'app_link_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN', 'ROLE_USER')]
    public function edit(Request $request, Link $link, LinkRepository $linkRepository): Response
    {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $link->setUpdatedAt(new \DateTime());
            $linkRepository->save($link, true);

            //            return $this->redirectToRoute('app_link_index', [], Response::HTTP_SEE_OTHER);
            $routeName = $request->attributes->get('_route');
            if ($routeName == 'app_link_index') {
                return $this->redirectToRoute('app_link_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_link_index_for_user', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('link/edit.html.twig', [
            'link' => $link,
            'form' => $form,
        ]);
    }


    #[Route('/link/{id}', name: 'app_link_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', 'ROLE_USER')]
    public function delete(Request $request, Link $link, LinkRepository $linkRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $link->getId(), $request->request->get('_token'))) {
            $linkRepository->remove($link, true);
        }

        $routeName = $request->attributes->get('_route');
        if ($routeName == 'app_link_index') {
            return $this->redirectToRoute('app_link_index', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->redirectToRoute('app_link_index_for_user', [], Response::HTTP_SEE_OTHER);
        }
    }
}