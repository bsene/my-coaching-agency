<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditProfileType;
use App\Form\EditPassWordType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommandRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="app_profile")
     */
    public function index()
    {
        return $this->render('profile/index.html.twig');
    }

    /**
     * Modifier le profil de l'utilisateur
     * 
     * @Route("/profile/edit", name="app_profile_edit")
     */
    public function editProfile(Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        $form = $this->createForm(EditProfileType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->flush();

            $this->addFlash('success', 'Les informations ont bien été modifiées');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit_profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Modifier le mot de passe
     * 
     * @Route("profile/editpassword", name="app_profile_editpassword")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @return void
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        $form = $this->createForm(EditPassWordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $em->flush();

            $this->addFlash('success', 'Le mot de passe a bien été modifié');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/command", name="app_profile_command")
     */
    public function profileCommande(CommandRepository $commandRepository)
    {
        $commands = $commandRepository->findAll();

        return $this->render('profile/command_profile.html.twig', [
            'commands' => $commands
        ]);
    }

    /**
     * @Route("/profile/article", name="app_profile_article")
     */
    public function profileArticle(ArticleRepository $articleRepository)
    {
        $articles = $articleRepository->findAll();

        return $this->render('profile/article_profile.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/profile/delete/{id<[0-9]+>}", name="app_profile_delete", methods={"DELETE"})
     */
    public function profileDelete(Request $request, EntityManagerInterface $em, User $user, TokenStorageInterface $tokenStorage, SessionInterface $session)
    {
        if($this->isCsrfTokenValid('profile_deletion_' . $user->getId(), $request->request->get('csrf_token'))){
            $tokenStorage->setToken(null);
            $session->invalidate();
            $em->remove($user);
            $em->flush();

            $this->addFlash('info', 'Profil supprimé');
        }

        return $this->redirectToRoute('app_home');
    }

    /** 
     * @Route("/profile/command/program", name="product_payed")
    */
    public function showProductPayed(CommandRepository $commandRepository, ProductsRepository $productsRepo)
    {
        $commands = $commandRepository->findAll();

        $program1 = $productsRepo->findBy(['title' => 'Programme musculation']);
        $program2 = $productsRepo->findBy(['title' => 'Programme Street-Workout']);
        $program3 = $productsRepo->findBy(['title' => 'Programme CrossFit']);

        return $this->render('profile/product_payed.html.twig', [
            'commands' => $commands,
            'program1' => $program1,
            'program2' => $program2,
            'program3' => $program3
        ]);
    }
}
