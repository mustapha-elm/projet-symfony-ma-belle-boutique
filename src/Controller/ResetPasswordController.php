<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);

            if ($user) {
                // 1: Enregistrer en BD la demande de reset_password avec user, token, createdAt
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTime());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                //2: Envoyer un email à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe

                $url = $this->generateUrl('update_password', ['token' => $reset_password->getToken()]);

                $to_email = $user->getEmail();
                $to_name = $user->getFirstName() . ' ' . $user->getLastName();
                $subject = 'Réinitialiser votre mot de passe sur Ma-Belle-Boutique.com';
                $content = 'Bonjour ' . $user->getFirstName() . ' ' . $user->getLastName() . ', <br>';
                $content .= 'Vous avez demandé à réinitiliser votre mot de passe sur le site Ma Belle Boutique. <br>/br>';
                $content .= '<a href="' . $url . '">Cliquez ici pour changer votre mot de passe</a>';

                $mail = new Mail();
                $mail->send($to_email, $to_name,  $subject,  $content);
                $this->addFlash('notice', 'Un Email vient de vous être envoyé pour réinitialiser votre mot de passe');
            } else {
                $this->addFlash('notice', 'Cette adresse Email est inconnue.');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update($token, Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneBy(['token' => $token]);

        if (!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }

        //Vérifier si le created At = now - 1h
        $now = new DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 1 hour'))
        //...dans ce cas le token a expiré
        {
            $this->addFlash('notice', 'Votre demande de mot de passe a expiré. Merci de la renouveler');
            $this->redirectToRoute('reset_password');
        }

        //Rendre une vue avec mot de passe et confirmer mot de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new_pwd = $form->get('new_password')->getData();
            //Encodage des mots de passe
            $password = $encoder->hashPassword($reset_password->getUser(), $new_pwd);
            //mise à jour du mot de passe de l'utilisateur
            $reset_password->getUser()->setPassword($password);
            //Flush en bd
            $this->entityManager->flush();
            //Redirection de l'utilisateur vers la page de connexion
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
