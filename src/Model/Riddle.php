<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use DateTime;
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getRiddle(): string
    {
        return $this->riddle;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }


    //------------------------------------------------------------------------------------------
    // SETTERS
    //------------------------------------------------------------------------------------------

    public function setUserId(int $id)
    {
        return $this->userId = $id;
    }

    public function setRiddle(string $riddle)
    {
        return $this->riddle = $riddle;
    }

    public function setAnswer(string $answer)
    {
        return $this->answer = $answer;
    }


    //------------------------------------------------------------------------------------------
    // JSON SERIALIZE
    //------------------------------------------------------------------------------------------

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}