<?php

namespace App\Controller;

use App\Form\EditProfileType;
use App\Entity\Annonces;
use App\Entity\Images;
use App\Form\AnnoncesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    /**
     * @Route("/users", name="users")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig');
    }
    
    /**
     * @Route("/users/profil/modifier", name="users_profil_modifier")
     */
    public function editeProfile(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis a Jour!');
            return $this->redirectToRoute('users');
        }
        return $this->render('user/editprofile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/annonces/ajout", name="users_annonces_ajout")
     */
    public function ajoutAnnonce(Request $request)
    {
        $annonce = new Annonces;

        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $annonce->setUsers($this->getUser());
            $annonce->setActive(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($annonce);
            $em->flush();

            return $this->redirectToRoute('users');
        }
        return $this->render('user/annonces/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/pass/modifier", name="users_pass_modifier")
     */
    public function editPass(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();

            // On vérifie si les 2 mots de passe sont identiques
            if($request->request->get('pass') == $request->request->get('pass2')){
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass')));
                $em->flush();
                $this->addFlash('message', 'Mot de passe mis à jour avec succès');

                return $this->redirectToRoute('users');
            }else{
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
            }
        }
        return $this->render('user/editpass.html.twig');
    }
}
