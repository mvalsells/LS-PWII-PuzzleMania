<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;

use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Service\ValidatorService;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class RiddlesAPIController
{
    public function __construct(
        private Twig           $twig
    ){}
}