<?php

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\UploadedFileInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;


use Ramsey\Uuid\Uuid;

class ProfileController
{
    private $twig;

    // Constants definitions of image parameters
    private const MAX_SIZE_IMAGE = 1024*1024;
    private const DIMENSION_IMAGE = 400;

    // Constant definition of images' directory
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads';

    // Constant definitions of possible errors
    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";
    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid";
    private const EXCEEDED_MAXIMUM_FILES_ERROR = "You can only upload one profile picture.";
    private const FILE_SIZE_ERROR = "The file '%s' uploaded can not exceed 1MB";
    private const IMAGE_DIMENSIONS_ERROR = "The file '%s' uploaded doesn't have the required dimensions (400x400)";
    private const NO_FILES_ERROR = "No file was uploaded";
    private const EMAIL_UPDATED_ERROR = "Sorry, the email address cannot be updated.";

    // We use this const to define the extensions that we are going to allow
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];
    private const DEFAULT_PROFILE_IMAGE = 'assets/images/defaultProfilePicture.png';


    public function __construct(
        Twig $twig,
    )
    {
        $this->twig = $twig;
    }
    public function show(Request $request, Response $response): Response
    {
        $data = [];
        // PROVISIONAL
        // $_SESSION["email"] = "aaah@gmail.com";
        // Set data variables to render the view
        $data["email"] = $_SESSION["email"];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'formAction' => $routeParser->urlFor('profile_post'),
                'formData' => $data,
                'profilePicture' => $_SESSION["profilePicturePath"] ?? self::DEFAULT_PROFILE_IMAGE
            ]
        );
    }
    public function handleForm(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedData = $request->getParsedBody();

        $data = [];
        // PROVISIONAL
        // $_SESSION["email"] = "aaah@gmail.com";
        $data["email"] = $_SESSION["email"];

        $errors = [];

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        /** @var UploadedFileInterface $uploadedFile */
        // Check if only one file was uploaded
        if (!empty($uploadedData["email"]) and $_SESSION["email"] !== $uploadedData["email"]) {
            $errors["email"] = self::EMAIL_UPDATED_ERROR;
        } else {
            if (count($uploadedFiles['files']) > 1) {
                $errors["profilePicture"] = self::EXCEEDED_MAXIMUM_FILES_ERROR;
            } elseif (!isset($uploadedFiles['files'])){ // TODO: not working
                $errors["profilePicture"] = self::NO_FILES_ERROR;
            } else {
                $uploadedFile = $uploadedFiles['files'][0];
                // Check there hasn't been any error in the submission
                if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                    $errors["profilePicture"] = sprintf(
                        self::UNEXPECTED_ERROR,
                        $uploadedFile->getClientFilename()
                    );
                } else {
                    // Get name of the file submitted
                    $name = $uploadedFile->getClientFilename();

                    // Get info from the submitted file
                    $fileInfo = pathinfo($name);

                    // Get image format
                    $format = $fileInfo['extension'];

                    // Check if the image format is valid
                    if (!$this->isValidFormat($format)) {
                        $errors["profilePicture"] = sprintf(self::INVALID_EXTENSION_ERROR, $format);
                    } else {
                        // Check the size of the image
                        $fileSize = $uploadedFile->getSize();
                        // Check the dimensions of the image
                        $imageInfo = getimagesize($uploadedFile->getFilePath());

                        if ($fileSize > self::MAX_SIZE_IMAGE) {
                            $errors["profilePicture"] = sprintf(self::FILE_SIZE_ERROR, $uploadedFile->getClientFilename());
                        } elseif ($imageInfo[0] !== self::DIMENSION_IMAGE || $imageInfo[1] !== self::DIMENSION_IMAGE) {
                            $errors["profilePicture"] = sprintf(self::IMAGE_DIMENSIONS_ERROR, $uploadedFile->getClientFilename());
                        }
                    }

                    // If no errors, we save the image
                    if (empty($errors)) {
                        $uuid = Uuid::uuid4();
                        $data["profilePicturePath"] = self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $uuid . "." . $format;
                        $_SESSION["profilePicturePath"] = 'uploads/' . $uuid . "." . $format;
                        // TODO: Update profile picture path in DDBB
                        $uploadedFile->moveTo($data["profilePicturePath"]);
                    }
                }
            }
        }

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'formErrors' => $errors ?? [],
                'formData' => $data,
                'profilePicture' => $_SESSION["profilePicturePath"] ?? self::DEFAULT_PROFILE_IMAGE,
                'formAction' => $routeParser->urlFor('profile_post')
            ]
        );
    }

    private function isValidFormat(string $extension): bool
    {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }
}