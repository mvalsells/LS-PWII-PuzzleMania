<?php

namespace Salle\PuzzleMania\Service;

class InputCheckerService
{

    public function checkInput(String $check) {

        if (count($check) > 30){
            return "[ERROR]: The input can't be longer than 30 characters.";
        }
        return null;
    }


}