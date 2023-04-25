<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\User;

final class MySQLUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    public function createUser(User $user): void
    {

        // Mirem si l'usuari està creat prèviament.
        if($this->exists($user)){
            print_r("User already created");
            return;
        }

        // Fem la query.
        $query = <<<'QUERY'
            INSERT INTO users(email, password, createdAt, updatedAt)
            VALUES(:email, :password, :createdAt, :updatedAt)
        QUERY;

        // Guardem els valors que volem.
        $email = $user->email();
        $password = $user->password();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);
        $updatedAt = $user->updatedAt()->format(self::DATE_FORMAT);

        $statement = $this->databaseConnection->prepare($query);

        // Li introduïm els valors a la query.
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('createdAt', $createdAt, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getUserByEmail(string $email)
    {
        $query = <<<'QUERY'
        SELECT * FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $row;
        }

        //TODO: Podriem fer flash messages per enviar controlar l'error de que apareguin varis emails iguals (aka si es lia parda perque no hauria de passar). -David
        return null;
    }

    public function getUserById(int $id)
    {
        $query = <<<'QUERY'
        SELECT * FROM users WHERE id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $row;
        }
        return null;
    }

    public function getAllUsers()
    {
        $query = <<<'QUERY'
        SELECT * FROM users
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        $users = [];

        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();

            for ($i = 0; $i < $count; $i++) {
                $user = User::create()
                    ->setId(intval($rows[$i]['id']))
                    ->setEmail($rows[$i]['email'])
                    //->setPassword($rows[$i]['password']) - don't ever expose pswd!!!!
                    ->setCreatedAt(date_create_from_format('Y-m-d H:i:s', $rows[$i]['createdAt']))
                    ->setUpdatedAt(date_create_from_format('Y-m-d H:i:s', $rows[$i]['updatedAt']));
                $users[] = $user;
            }
        }
        return $users;
    }

    public function getTeamByID(int $id)
    {

    }

    /*public function addUserToTeam(int $id)
    {
        INSERT INTO teams (team_id, user_id, score) VALUES
    (1, (SELECT users.id FROM users WHERE users.email = 'prova' LIMIT 1), 0)
    }*/

    /***
     * Function that creates a team given 2 users.
     * @param User $u1
     * @param User $u2
     * @return void
     */
    public function createTeam(User $u1, User $u2){

        // Mirem si l'usuari està registrat en un equip.
        if($this->hasTeam($u1) || $this->hasTeam($u2)){
            print_r("USER ALREADY REGISTERED"); //TODO: Flash message.
            return;
        }

        // Creem l'equip.
        $query = <<<'QUERY'
            INSERT INTO teams (user_id_1, user_id_2, score) VALUES
            ((SELECT users.id FROM users WHERE users.email = :email1 LIMIT 1),   
            (SELECT users.id FROM users WHERE users.email = :email2 LIMIT 1), 0);
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Posem els paràmetres.
        $email1 = $u1->email();
        $email2 = $u2->email();
        $statement->bindParam(':email1', $email1, PDO::PARAM_STR);
        $statement->bindParam(':email2', $email2, PDO::PARAM_STR);

        $statement->execute();

    }

    public function createSoloTeam(User $u1): void
    {

        // Mirem si l'usuari està registrat en un equip.
        if($this->hasTeam($u1)){
            print_r("USER ALREADY REGISTERED"); //TODO: Flash message.
            return;
        }

        // Creem l'equip.
        $query = <<<'QUERY'
            INSERT INTO teams (user_id_1, user_id_2, score) VALUES
            ((SELECT users.id FROM users WHERE users.email = :email LIMIT 1), null, 0);
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Posem els paràmetres.
        $email1 = $u1->email();
        $statement->bindParam(':email', $email1, PDO::PARAM_STR);

        $statement->execute();
    }

    /***
     * Function that checks if a user is registered on a tema or not
     * @param User $u
     * @return bool
     */
    public function hasTeam(User $u)
    {
        // Busquem l'usuari a la BBDD per a aconseguir l'ID.
        $user = $this->getUserByEmail($u->email());

        // Mirem si l'usuari està a la BBDD.
        if($user == null){
            echo "User not created"; //TODO: Flash message.
            return false;
        }

        // Mirem si hi ha algun equip amb l'usuari que estem registrant.
        $query = <<<'QUERY'
            SELECT COUNT(*) FROM teams WHERE user_id_1 = :id OR user_id_2 = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Busquem la id de l'usuari.
        $id = $user->id;

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        // Mirem quants equips tenen aquest usuari.
        $count = $statement->fetch(PDO::FETCH_ASSOC);

        // Si l'usuari ja està registrat tornem true.
        if($count['COUNT(*)'] > 0) return true;
        return false;

    }

    private function addToTeamByID(int $teamId, User $user){

    }

    /**
     * Funció que afegeix un usuari a un equip ja existent.
     * @param User $oldUser
     * @param User $newUser
     * @return void
     */
    private function addToTeam(User $oldUser, User $newUser){

        //TODO: mirar que no hi hagi dues persones ja registrades.

        // Mirem que els usuaris existeixin.
        if($this->exists($oldUser) && $this->exists($newUser)){

            // Mirem que el primer usuari tingui equip i que el segon no.
            if($this->hasTeam($oldUser) && !$this->hasTeam($newUser)){


                //TODO: agafar id d'equips
                /*$this->getTeamID($u);

                $query = <<<'QUERY'
                    UPDATE teams
                    SET user_id_2 = :id
                    WHERE team_id = 1;
                QUERY;

                $statement = $this->databaseConnection->prepare($query);

                // Busquem la id de l'usuari.
                $id = $user->id;

                $statement->bindParam('id', $id, PDO::PARAM_INT);

                $statement->execute();

                // Mirem quants equips tenen aquest usuari.
                $count = $statement->fetch(PDO::FETCH_ASSOC);*/



            }elseif ($this->hasTeam($newUser)){
                print_r("The new user can't be added to the new team, it's already in one"); //TODO: flash message
                return;
            }
        }
    }

    private function exists(User $user)
    {
        $user = $this->getUserByEmail($user->email());

        // Mirem si l'usuari està a la BBDD.
        if($user == null){
            echo "User not created"; //TODO: Flash message.
            return false;
        }

        return true;
    }

    public function getTeamID(User $u)
    {

        // Busquem l'usuari a la BBDD per a aconseguir l'ID.
        $user = $this->getUserByEmail($u->email());

        // Mirem si l'usuari està a la BBDD.
        if($user == null){
            echo "User not created"; //TODO: Flash message.
            return null;
        }

        $query = <<<'QUERY'
            SELECT team_id FROM teams WHERE user_id_1 = :id1 OR user_id_2 = :id2 LIMIT 1;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Busquem la id de l'usuari.
        $id = $user->id;

        $statement->bindParam('id1', $id, PDO::PARAM_INT);
        $statement->bindParam('id2', $id, PDO::PARAM_INT);


        $statement->execute();

        // Mirem quants equips tenen aquest usuari.
        return $statement->fetch(PDO::FETCH_ASSOC);

    }


}
