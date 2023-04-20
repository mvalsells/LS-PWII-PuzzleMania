<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;

use Slim\Views\Twig;

class UsersAPIController
{
    public function __construct(
        private Twig           $twig
    ){}
}