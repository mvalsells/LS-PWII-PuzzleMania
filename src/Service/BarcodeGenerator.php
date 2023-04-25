<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Service;

use GuzzleHttp;

class BarcodeGenerator
{
    private GuzzleHttp\Client $client;
    public function __construct(){
        $this->client = new GuzzleHttp\Client([
            //TODO preguntar si nom del container és correcte
            'base_uri' => "http://pw_barcode_pjI"
        ]);
    }

    public function simpleQRBase64(string $data): string{
        $response = $this->client->post('/BarcodeGenerator', [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'image/jpeg'
            ],
            GuzzleHttp\RequestOptions::JSON => [
                'symbology' => 'QRCode',
                'code' => $data,
            ]
        ]);
        $imgData = base64_encode($response->getBody()->getContents());
        return 'data:image/jpeg;base64,' . $imgData;
    }
}