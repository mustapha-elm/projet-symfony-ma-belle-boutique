<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

require './../vendor/autoload.php';

use \Mailjet\Resources;

class TestEmailController extends AbstractController
{
    /**
     * @Route("/test/email", name="test_email")
     */
    public function index()
    {

        $mj = new \Mailjet\Client('76cfcd8470f38d90cff4bb8290f6f41d', 'a0a52d8b24b776042639d17d868b241a', true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "mustapha.elm@hotmail.com",
                        'Name' => "MUSTAPHA"
                    ],
                    'To' => [
                        [
                            'Email' => "zemou59@hotmail.fr",
                            'Name' => "MUSTAPHA"
                        ]
                    ],
                    'Subject' => "Greetings from Mailjet.",
                    'TextPart' => "My first Mailjet email",
                    'HTMLPart' => "<h3>Dear passenger 1, welcome to <a href='https://www.mailjet.com/'>Mailjet</a>!</h3><br />May the delivery force be with you!",
                    'CustomID' => "AppGettingStartedTest"
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }
}
