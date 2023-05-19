<?php
/**
 * Profile Controller: Manages the profile view logic (QR, info to show and authorization)
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 20/04/2023
 * @updated: 19/05/2023
 */
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
    private const INVALID_MIME_ERROR = "The received file is not valid";
    private const EXCEEDED_MAXIMUM_FILES_ERROR = "You can only upload one profile picture.";
    private const FILE_SIZE_ERROR = "The file '%s' uploaded can not exceed 1MB";
    private const IMAGE_DIMENSIONS_ERROR = "The file '%s' uploaded doesn't have the required dimensions (400x400)";
    private const NO_FILES_ERROR = "No file was uploaded";
    private const EMAIL_UPDATED_ERROR = "Sorry, the email address cannot be updated.";

    // We use this constants to define the extensions that we are going to allow and the default profile image for all users
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];
    private const DEFAULT_PROFILE_IMAGE = 'assets/images/defaultProfilePicture.png';


    // Constructor
    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
    )
    {
    }

    /**
     * Function that renders the profile page with the correct information
     * @param Request $request Used for route parser, to determine the 'profile_post' link
     * @param Response $response Variable that will contain the render twig
     * @return Response The twig view to render with the different parameters.
     */
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

    /**
     * Function that handles the profile view form, where users can update their profile picture
     * @param Request $request Used for route parser, to determine the 'profile_post' link
     * @param Response $response Variable that will contain the render twig
     * @return Response The twig view to render with the different parameters.
     */
    public function handleForm(Request $request, Response $response): Response
    {
        // Get uploaded files and uploaded data in the form
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
        $notifications = [];

        // Check if profile picture is located in server (in case it exists)
        if (isset($_SESSION["profilePicturePath"]) and file_exists($_SESSION["profilePicturePath"])) {
            $picturePath = $_SESSION["profilePicturePath"];
        }

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'notifs' => $notifications,
                'formErrors' => $errors ?? [],
                'email' => $_SESSION["email"],
                "team" => $_SESSION['team_id'] ?? null,
                'profilePicture' => $picturePath ?? self::DEFAULT_PROFILE_IMAGE,
                'formAction' => $routeParser->urlFor('profile_post')
            ]
        );
    }

    /**
     * Function that checks fow many files have been uploaded
     * @param array $errors Array that contains the previous errors detected
     * @param array $uploadedFiles Array that contains the uploaded files
     * @return array Errors array updated if the number of files is not correct
     */
    private function checkNumberOfFiles(array $errors, array $uploadedFiles): array
    {
        if (!isset($uploadedFiles['files'])) {
            $errors["profilePicture"] = self::NO_FILES_ERROR;
        } elseif (count($uploadedFiles['files']) > 1) {
            $errors["profilePicture"] = self::EXCEEDED_MAXIMUM_FILES_ERROR;
        } elseif (!isset($uploadedFiles['files'][0]) || $uploadedFiles['files'][0]->getError() !== UPLOAD_ERR_OK){
            $errors["profilePicture"] = self::NO_FILES_ERROR;
        }
        return $errors;
    }

    /**
     * Function that checks the uploaded file to see there aren't any errors
     * @param array $errors Array that contains the previous errors detected
     * @param UploadedFileInterface $uploadedFile File uploaded in the form
     * @return array Errors array updated if the number of files is not correct
     */
    private function checkUploadedFile(array $errors, UploadedFileInterface $uploadedFile): array
    {
        // Get name of the file submitted
        $name = $uploadedFile->getClientFilename();

        // Get info from the submitted file
        $fileInfo = pathinfo($name);

        // Get image format
        $format = $fileInfo['extension'];

        // Check if mimeType is correct
        if(!$this->checkMime($uploadedFile)){
            $errors["profilePicture"] = self::INVALID_MIME_ERROR;
        }else{
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
        }

        // If no errors, we save the image
        if (empty($errors)) {
            $this->saveProfilePicture($uploadedFile, $format);
        }

        return $errors;
    }

    /**
     * Function that saves the uploaded file as the new profile picture of the user
     * @param UploadedFileInterface $uploadedFile File uploaded in the form
     * @param string $format Format of the uploaded file
     * @return void -
     */
    private function saveProfilePicture(UploadedFileInterface $uploadedFile, string $format): void
    {
        // Generate uuid for new profile picture
        $uuid = Uuid::uuid4();
        // Delete past profile picture if exists
        if (isset($_SESSION["profilePicturePath"])) {
            $past_picture = __DIR__ . '/../../public/' . $_SESSION["profilePicturePath"];
            unlink($past_picture);
        }

        // Create new profile picture path and save as a session variable
        $_SESSION["profilePicturePath"] = 'uploads/' . $uuid . "." . $format;

        // Upload new profile picture path to database
        $this->userRepository->updateProfilePicture($_SESSION['user_id'], $_SESSION["profilePicturePath"]);

        // Check if the /uploads directory exists, and if not create it
        if (!is_dir(self::UPLOADS_DIR)) {
            mkdir(self::UPLOADS_DIR, 0777, true);
        }

        // Save new profile picture in 'uploads/' folder
        $uploadedFile->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $uuid . "." . $format);
    }

    /**
     * Function that checks the uploaded file is a valid format
     * @param string $extension Extension of the uploaded file
     * @return bool Variable that indicates whether the file is supported (=true) or not (=false)
     */
    private function isValidFormat(string $extension): bool
    {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    /**
     * Function that checks the uploaded file is actually one of the supported types for the profile picture
     * @param UploadedFileInterface $uploadedFile File uploaded in the form
     * @return bool Variable that indicates whether the file is supported (=true) or not (=false)
     */
    private function checkMime(UploadedFileInterface $uploadedFile): bool
    {
        if(strcmp($uploadedFile->getClientMediaType(), "image/jpg") == 0 
           || strcmp($uploadedFile->getClientMediaType(), "image/png") == 0) {
            return true;
        }
        return false;
    }
}