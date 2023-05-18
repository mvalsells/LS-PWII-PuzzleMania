<?php
/**
 * Riddle: models the Riddle structure that contains relevant info (riddle, answer, riddle ID and creator info).
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 25/04/2023
 * @updated: 18/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use JsonSerializable;

class Riddle implements JsonSerializable
{

    //------------------------------------------------------------------------------------------
    // VARIABLES
    //------------------------------------------------------------------------------------------

    private ?int $id;
    private ?int $userId;
    private string $riddle;
    private string $answer;


    //------------------------------------------------------------------------------------------
    // CONSTRUCTORS
    //------------------------------------------------------------------------------------------

    public function __construct(
        ?int     $id,
        ?int    $userId,
        string $riddle,
        string $answer
    )
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->riddle = $riddle;
        $this->answer = $answer;
    }

    //------------------------------------------------------------------------------------------
    // GETTERS
    //------------------------------------------------------------------------------------------
    /**
     * @return ?int ID of the user (or null if no user is associated as creator)
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @return string Riddle
     */
    public function getRiddle(): string
    {
        return $this->riddle;
    }

    /**
     * @return string Answer of the riddle
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }

    /**
     * @return int Riddle ID
     */
    public function getId(): int
    {
        return $this->id;
    }


    //------------------------------------------------------------------------------------------
    // SETTERS
    //------------------------------------------------------------------------------------------
    /**
     * @param int $id ID of the creator user
     */
    public function setUserId(int $id): void
    {
        $this->userId = $id;
    }

    /**
     * @param string $riddle Riddle
     */
    public function setRiddle(string $riddle): void
    {
        $this->riddle = $riddle;
    }

    /**
     * @param string $answer Answer of the riddle
     */
    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    /**
     * @param int $id Riddle ID
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }


    //------------------------------------------------------------------------------------------
    // JSON SERIALIZE
    //------------------------------------------------------------------------------------------
    /**
     * @return array Json array of the riddle data
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}