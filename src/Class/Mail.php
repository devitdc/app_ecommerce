<?php

namespace App\Class;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private string $apiKey = "8b0d679365d9b8927fdcedf9d1ecceb0";
    private string $apiKeySecret = "d8f6c3c7842b38b47e537d297ae7fdf2";

    public function sendInfo($emailTo, $emailToName, $subject, $customerName, $templateID, $link = "https://localhost:8000" ): void
    {
        $mj = new Client($this->apiKey, $this->apiKeySecret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact@it-dc.fr",
                        'Name' => "Service Client"
                    ],
                    'To' => [
                        [
                            'Email' => $emailTo,
                            'Name' => $emailToName
                        ]
                    ],
                    'TemplateID' => $templateID,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'customerName' => "$customerName",
                        'confirmationLink' => $link
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
        /*$response->success() && dd($response->getData());*/
    }

    public function sendCheckOutSuccess($emailTo, $emailToName, $options ): void
    {
        $mj = new Client($this->apiKey, $this->apiKeySecret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact@it-dc.fr",
                        'Name' => "Service Client"
                    ],
                    'To' => [
                        [
                            'Email' => $emailTo,
                            'Name' => $emailToName
                        ]
                    ],
                    'TemplateID' => 4637328,
                    'TemplateLanguage' => true,
                    'Variables' => [
                        'orderNumero' => $options['orderNumero'],
                        'customerName' => $options['customerName'],
                        'deliveryDelay' => $options['deliveryDelay'],
                        'deliveryAddress' => $options['deliveryAddress'],
                        'deliveryType' => $options['deliveryType'],
                        'total' => $options['orderTotal']
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}

