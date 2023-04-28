<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Model\User;

interface RiddleRepository
{

    public function getAllRiddles(): array;
    public function addRiddle(Riddle $r): void;
    public function updateRiddle(int $originalId, Riddle $newRiddle): void;
    public function getOneRiddleById(int $id): ?Riddle;
    public function deleteRiddle(int $id): void;
}