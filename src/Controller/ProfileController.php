<?php

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\UploadedFileInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;


use Ramsey\Uuid\Uuid;

class ProfileController
{
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
        private Twig           $twig,
        private UserRepository $userRepository,
    )
    {
    }
    public function show(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'formAction' => $routeParser->urlFor('profile_post'),
                'email' => $_SESSION["email"],
                "team" => $_SESSION['team_id'] ?? null,
                'profilePicture' => $_SESSION["profilePicturePath"] ?? self::DEFAULT_PROFILE_IMAGE
            ]
        );
    }
    public function handleForm(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedData = $request->getParsedBody();

        $errors = [];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        /** @var UploadedFileInterface $uploadedFile */
        // Check the email field has not changed
        if (!empty($uploadedData["email"]) and $_SESSION["email"] !== $uploadedData["email"]) {
            $errors["email"] = self::EMAIL_UPDATED_ERROR;
        } else {
            // Check if only one file was uploaded
            $errors = $this->checkNumberOfFiles($errors, $uploadedFiles);
            if (empty($errors["profilePicture"])) {
                $uploadedFile = $uploadedFiles['files'][0];
                // Check there hasn't been any error in the submission
                if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                    $errors["profilePicture"] = sprintf(
                        self::UNEXPECTED_ERROR,
                        $uploadedFile->getClientFilename()
                    );
                } else {
                    // Check the file is correct
                    $errors = $this->checkUploadedFile($errors, $uploadedFile);
                }
            }
        }

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'formErrors' => $errors ?? [],
                'email' => $_SESSION["email"],
                "team" => $_SESSION['team_id'] ?? null,
                'profilePicture' => $_SESSION["profilePicturePath"] ?? self::DEFAULT_PROFILE_IMAGE,
                'formAction' => $routeParser->urlFor('profile_post')
            ]
        );
    }

    private function checkNumberOfFiles(array $errors, array $uploadedFiles): array
    {
        if (count($uploadedFiles['files']) > 1) {
            $errors["profilePicture"] = self::EXCEEDED_MAXIMUM_FILES_ERROR;
        } elseif (!isset($uploadedFiles['files'][0]) || $uploadedFiles['files'][0]->getError() !== UPLOAD_ERR_OK){ // TODO: not working
            $errors["profilePicture"] = self::NO_FILES_ERROR;
        }
        return $errors;
    }

    private function checkUploadedFile(array $errors, UploadedFileInterface $uploadedFile): array
    {
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
            // Generate uuid for new profile picture
            $uuid = Uuid::uuid4();
            // TODO: delete past profile picture correct
            $past_picture = __DIR__ . '/../../public/' . $_SESSION["profilePicturePath"];
            unlink($past_picture);
            $_SESSION["profilePicturePath"] = 'uploads/' . $uuid . "." . $format;
            // Upload new profile picture path to database
            $this->userRepository->updateProfilePicture($_SESSION['user_id'], $_SESSION["profilePicturePath"]);
            // Save new profile picture in 'uploads/' folder
            $uploadedFile->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $uuid . "." . $format);
        }

        return $errors;
    }

    private function isValidFormat(string $extension): bool
    {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }
}