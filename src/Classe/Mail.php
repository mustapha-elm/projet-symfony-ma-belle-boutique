<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $api_key = '76cfcd8470f38d90cff4bb8290f6f41d';
    private $api_key_secret = 'a0a52d8b24b776042639d17d868b241a';

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "mustapha.elm@hotmail.com",
                        'Name' => "Ma Belle Boutique"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3338923,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]

        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}
