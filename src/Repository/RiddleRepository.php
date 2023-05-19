<?php
/**
 * RiddleRepository: Interface that defines the methods to access data sources that contain Riddle instances
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 20/04/2023
 * @updated: 18/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Riddle;

interface RiddleRepository
{
    /**
     * Returns an array of Model/Riddle objects with all the available riddles.
     * @return array Array of Riddle instances
     */
    public function getAllRiddles(): array;

    /**
     * Adds the Riddle object to the repository, and return the 'ID' of the added riddle.
     * @param Riddle $r Riddle object to persist, where the 'id' and 'userId' attributes can be null.
     * @return int The ID associated to the added riddle
     */
    public function addRiddle(Riddle $r): int;

    /**
     * Updates the riddle with ID 'originalId' in the repository with the 'newRiddle' data
     * @param int $originalId 'id' of the riddle to update
     * @param Riddle $newRiddle Riddle instance where info to update is contained: 'id' attribute CANNOT be null, while
     *                          'userId' can be null. The unchanged fields must have the original information.
     * @return void -
     */
    public function updateRiddle(int $originalId, Riddle $newRiddle): void;

    /**
     * Given an id of a riddle, a Model/Riddle object with all the information is returned. If the riddle is not found
     * in the repository null is returned.
     * @param int $id id of the riddle to get from repository
     * @return Riddle|null The riddle associated to the 'id' provided, or null if no riddle is associated to the 'id'
     */
    public function getOneRiddleById(int $id): ?Riddle;

    /**
     * Given an 'id' of a riddle this one is deleted from the repository
     * @param int $id 'id' of the riddle to delete from the repository
     * @return void -
     */
    public function deleteRiddle(int $id): void;
}