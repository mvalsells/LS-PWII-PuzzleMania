<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Service;

class ValidatorService
{
    public function __construct()
    {
    }

    public function validateEmail(string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'The email address is not valid.';
        } else if (!strpos($email, "@salle.url.edu")) {
            return 'Only emails from the domain @salle.url.edu are accepted.';
        }
        return '';
    }

    public function validatePassword(string $password): string
    {
        if (empty($password) || strlen($password) < 6) {
            return 'The password must contain at least 7 characters.';
        } else if (!preg_match("~[0-9]+~", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[A-Z]/", $password)) {
            return 'The password must contain both upper and lower case letters and numbers.';
        }
        return '';
    }

    public function validateBirthday(string $bday)
    {
        if ($bday == '') {
            return '';
        }
        $bday_aux = explode('-', $bday);
        if (!checkdate(intval($bday_aux[1]), intval($bday_aux[2]), intval($bday_aux[0]))) {
            return 'Birthday is invalid';
        }
        $year = $bday_aux[0];
        if ((date('Y') - $year) <= 18) {
            return 'Sorry, you are underage';
        }

    }
}
