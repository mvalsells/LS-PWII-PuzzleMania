<?php
/**
 * Barcode class: Provides access to the BarcodePro Web API for Docker by Neodynamic
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 24/05/2023
 * @updated: 25/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Service;

use GuzzleHttp;

class BarcodeGenerator
{
    private GuzzleHttp\Client $client;

    /**
     * Constructor for a BarcodeGenerator object
     */
    public function __construct(){
        $this->client = new GuzzleHttp\Client([
            'base_uri' => "http://barcode"
        ]);
    }

    /**
     * Generates QR code with the API
     * @param string $data Data to be found in the QR code
     * @return string|null Image with the mime and data encoded in base64, ready to be placed in the src attribute of
     *                     an img tag. If there was an error generating the code null is returned.
     */
    public function simpleQRBase64(string $data): ?string{
        try {
            $response = $this->client->post('/BarcodeGenerator', [
                // Answer must be a jpeg file
                GuzzleHttp\RequestOptions::HEADERS => [
                    'Accept' => 'image/jpeg'
                ],
                // Barcode format and data
                GuzzleHttp\RequestOptions::JSON => [
                    'symbology' => 'QRCode',
                    'code' => $data,
                    'artFinderShape' => 'RoundRect',
                ]
            ]);
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            return null;
        }
        // Get the image data from the request and encode it in base64
        $imgData = base64_encode($response->getBody()->getContents());
        return 'data:image/jpeg;base64,' . $imgData;
    }
}