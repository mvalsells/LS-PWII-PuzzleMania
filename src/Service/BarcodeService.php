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

class BarcodeService
{
    private GuzzleHttp\Client $client;

    private const QRCODES_DIR = __DIR__ . '/../../public/QR_codes';

    /**
     * Constructor for a BarcodeService object
     */
    public function __construct(){
        $this->client = new GuzzleHttp\Client([
            'base_uri' => "http://barcode"
        ]);
    }

    /**
     * Generates QR code with the API
     * @param string $data Data to be found in the QR code
     * @return bool Indicates whether the QR has been received correctly from the API or not
     */
    public function generateSimpleQR(string $data, string $text): bool {
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
                    'humanReadableText' => $text,
                ]
            ]);
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            return false;
        }
        // Get the image data from the request and encode it in base64
        $imgData = ($response->getBody()->getContents());
        $filePath = self::QRCODES_DIR . DIRECTORY_SEPARATOR . $_SESSION['team_id'] . ".jpeg";

        // Check if the /qrcodes directory exists, and if not create it
        if (!is_dir(self::QRCODES_DIR)) {
            mkdir(self::QRCODES_DIR, 0777, true);
        }
        file_put_contents($filePath, $imgData);

        return true;
    }

    public function getQRFilePath(int $id): string
    {
        return self::QRCODES_DIR . DIRECTORY_SEPARATOR . $id . ".jpeg";
    }
}