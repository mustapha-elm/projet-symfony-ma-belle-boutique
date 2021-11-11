<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * @Route("/commande/create-session/{reference}", name="stripe_create_session")
     */
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
    {

        Stripe::setApiKey('sk_test_51JjUHOA9aZ1JuFYu1iq0942UleBiLmRu4L7xs2RIGcZBIpjjAtUJar9khXJ7Vpxuqi2yBlOQw9WveWJb5g6dIZNT00clxMC8a2');

        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://localhost:8000';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);



        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN . '/uploads/' . $product_object->getIllustration()]
                    ]
                ],
                'quantity' => $product->getQuantity()
            ];
        }

        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN]
                ]
            ],
            'quantity' => 1
        ];




        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [$products_for_stripe],
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush();

        //dump($checkout_session->id);
        //dd($checkout_session);

        //$response = new JsonResponse(['id' => $checkout_session->id]);
        return $this->redirect($checkout_session->url);
    }
}
