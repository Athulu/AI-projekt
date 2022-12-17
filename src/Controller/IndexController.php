<?php

namespace App\Controller;

use App\Entity\Link;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[Route('/')]
class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET'])]
    public function index(Request $request, LinkRepository $linkRepository): Response
    {
        $link = new Link();

        $form = $this->createFormBuilder($link)
            ->add('fullLink', TextType::class, ['required' => true])
            ->add('link', TextType::class, ['required' => false])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $link->setCreatedAt(new \DateTime());
            $link->setUpdatedAt(new \DateTime());
            $link->setUsesCount(0);
            $link->setIsActive(true);
            if ($link->getLink() == null) {
                $link->setLink(hash('crc32', $link->getFullLink()));
            }

            $linkRepository->save($link, true);

            $this->addFlash('successGetLink', $link->getFullLink());

            return $this->redirectToRoute('app_index');
        }

        return $this->render(
            'index/index.html.twig',
            [
                'form' => $form->createView(),
                'links' => $linkRepository->findAll(),
            ]
        );
    }
}