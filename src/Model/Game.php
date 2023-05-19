<?php
/**
 * Game: models the Game structure that contains relevant info (game ID and riddles).
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 16/05/2023
 * @updated: 18/05/2023
 */
namespace Salle\PuzzleMania\Model;

class Game
{
    //------------------------------------------------------------------------------------------
    // VARIABLES
    //------------------------------------------------------------------------------------------

    private int $user_id;
    private int $riddle_1;
    private int $riddle_2;
    private int $riddle_3;


    //------------------------------------------------------------------------------------------
    // CONSTRUCTORS
    //------------------------------------------------------------------------------------------

    public function __construct ($user_id, $riddle_1, $riddle_2, $riddle_3) {
        $this->user_id = $user_id;
        $this->riddle_1 = $riddle_1;
        $this->riddle_2 = $riddle_2;
        $this->riddle_3 = $riddle_3;
    }

    //------------------------------------------------------------------------------------------
    // GETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @return int User Id
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @return int Riddle 1 Id
     */
    public function getRiddle1(): int
    {
        return $this->riddle_1;
    }

    /**
     * @return int Riddle 2 Id
     */
    public function getRiddle2(): int
    {
        return $this->riddle_2;
    }

    /**
     * @return int Riddle 3 Id
     */
    public function getRiddle3(): int
    {
        return $this->riddle_3;
    }
}