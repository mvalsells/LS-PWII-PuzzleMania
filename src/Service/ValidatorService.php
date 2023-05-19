<?php
/**
 * Validator class: Defines functions that are used to check the user's inputs in the webpage
 * @author: Marc Valsells, Òscar de Jesus and David Larrosa
 * @creation: 28/04/2023
 * @updated: 18/05/2023
 */
declare(strict_types=1);

namespace Salle\PuzzleMania\Service;

class ValidatorService
{
    // Maximum size of the input fields
    const MAX_INPUT_SIZE = 100;

    /**
     * Constructor for a ValidatorService object
     */
    public function __construct()
    {
    }

    /**
     * Validates the user's email provided
     * @param string $email Email introduced by the user
     * @return string Error message (if any error has arisen during check, if not it returns an empty string)
     */
    public function validateEmail(string $email): string
    {
        if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'The email address is not valid.';
        } else if ($this->checkIfInputTooLong($email)){
            return "The email can't be longer than " . self::MAX_INPUT_SIZE . " characters.";
        }else if (!strpos($email, "@salle.url.edu")) {
            return 'Only emails from the domain @salle.url.edu are accepted.';
        }
        return '';
    }

    /**
     * Validates the user's password provided
     * @param string $password Password introduced by the user
     * @return string Error message (if any error has arisen during check, if not it returns an empty string)
     */
    public function validatePassword(string $password): string
    {
        if (empty($password) || strlen($password) < 6) {
            return 'The password must contain at least 7 characters.';
        } else if ($this->checkIfInputTooLong($password)) {
            return "The password can't be longer than " . self::MAX_INPUT_SIZE . " characters.";
        } else if (!preg_match("~[0-9]+~", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[A-Z]/", $password)) {
            return 'The password must contain both upper and lower case letters and numbers.';
        }
        return '';
    }

    /**
     * Validates the user's input provided
     * @param string $input Input introduced by the user
     * @return bool Variable that indicates if the input is larger than the maximum allowed (=true) or not (=false)
     */
    public function checkIfInputTooLong(string $input): bool
    {
        return strlen($input) > self::MAX_INPUT_SIZE;
    }
}
