<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Service;

use GuzzleHttp;

class BarcodeGenerator
{
    private GuzzleHttp\Client $client;
    public function __construct(){
        $this->client = new GuzzleHttp\Client([
            'base_uri' => "http://barcode"
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
                'artFinderShape' => 'RoundRect',
            ]
        ]);
        $imgData = base64_encode($response->getBody()->getContents());
        return 'data:image/jpeg;base64,' . $imgData;
    }
}