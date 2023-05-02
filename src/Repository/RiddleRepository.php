<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Model\User;

interface RiddleRepository
{
    /**
     * Returns an array of Model/Riddle objects with all the available riddles.
     * @return array
     */
    public function getAllRiddles(): array;

    /**
     * Adds the $r object to the repository, the ID of the added riddle is returned.
     * @param Riddle $r The 'id' and 'userId' attributes can be null.
     * @return int
     */
    public function addRiddle(Riddle $r): int;

    /**
     * Updates the riddle with ID $originalId in the repository with the $newRiddle data
     * @param int $originalId
     * @param Riddle $newRiddle 'id' attribute CANNOT be null, 'userId' can be null. The unchanged fields must have the
     *                          original information.
     * @return void
     */
    public function updateRiddle(int $originalId, Riddle $newRiddle): void;

    /**
     * Given an id of a riddle, a Model/Riddle object with all the information is returned. If the riddle is not found
     * in the repository null is returned.
     * @param int $id
     * @return Riddle|null
     */
    public function getOneRiddleById(int $id): ?Riddle;

    /**
     * Given an id of a riddle this one is deleted from the repository
     * @param int $id
     * @return void
     */
    public function deleteRiddle(int $id): void;
}