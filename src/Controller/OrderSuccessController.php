<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_success")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($order->getState() == 0) {
            //Vider la session Cart
            $cart->remove();
            //Modifier le statut isPaid de notre commande en mettant 1 puisque le paiement est reussi
            $order->setState(1);
            $this->entityManager->flush();

            //Envoyer un mail au client pour lui confirmer sa commande
            $mail = new Mail();
            $to_email = $order->getUser()->getEmail();
            $to_name = $order->getUser()->getFirstName() . ' ' . $order->getUser()->getLastName();
            $subject = 'Votre commande Ma Belle Boutique est validée';
            $content = 'Bonjour ' . $order->getUser()->getFirstName() . ', merci pour votre commande <br> Lorem, ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit quos alias, quae, temporibus odit, eligendi aspernatur at labore doloremque provident recusandae cupiditate architecto atque porro vitae cum qui consectetur quisquam?';
            $mail->send($to_email, $to_name, $subject, $content);
        }



        //Afficher les queques infos de la commande de l utilisateur



        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
