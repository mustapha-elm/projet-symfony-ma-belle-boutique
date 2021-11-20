<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/nous-contacter", name="contact")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('notice', 'Merci de nous avoir contacté. Notre équipe vous répondra dans les meilleurs délais.');
            $dataForm = $form->getData();
            //envoyer un mail avec les données du contact ou
            $mail = new Mail();
            $to_email = 'zemou59@hotmail.fr';
            $to_name = 'De ' . $dataForm['nom'] . ' - ' . $dataForm['email'];
            $subject = 'demande contact MaBelleBoutique';
            $content = $dataForm['content'];
            $mail->send($to_email, $to_name, $subject, $content);
            //enregistrer en base de données
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
