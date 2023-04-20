<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\User;

interface RiddleRepository
{
    public function getRiddle();
}