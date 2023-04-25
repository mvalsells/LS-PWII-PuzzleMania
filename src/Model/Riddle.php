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

    private int $idRiddle;
    private int $idUser;
    private string $riddle;
    private string $answer;


    //------------------------------------------------------------------------------------------
    // CONSTRUCTORS
    //------------------------------------------------------------------------------------------

    public function __construct(
        int $idUser,
        string $riddle,
        string $answer
    )
    {
        $this->idUser = $idUser;
        $this->riddle = $riddle;
        $this->answer = $answer;
    }

    //------------------------------------------------------------------------------------------
    // GETTERS
    //------------------------------------------------------------------------------------------

    public function getIdUser(){
        return $this->idUser;
   }

    public function getRiddle(){
        return $this->riddle;
    }

    public function getAnswer(){
        return $this->answer;
    }


    //------------------------------------------------------------------------------------------
    // SETTERS
    //------------------------------------------------------------------------------------------

    public function setIdUser(int $id){
        return $this->idUser = $id;
    }

    public function setRiddle(string $riddle){
        return $this->riddle = $riddle;
    }

    public function setAnswer(string $answer){
        return $this->answer = $answer;
    }


    //------------------------------------------------------------------------------------------
    // JSON SERIALIZE
    //------------------------------------------------------------------------------------------

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
