<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\User;

/**
 *  TODO: FER NOMS D'EQUP Aka editar taula equips
 *  TODO: Coses que cal fer a la BBDD (✔✖)
 *
 *      Login i register:
 *      · Registre d'usuari --> ✔
 *      · Comprovació existència d'usuari --> ✔
 *
 *      Teams:
 *      · Creació d'equip sencer --> ✔
 *      · Creació d'equip individual --> ✔
 *      · Afegir usuari a equip
 *          · Per usuari ja existent --> ✔
 *          · Per ID d'equip --> ✔
 *      · Buscar si un usuari té equip --> ✔
 *      · Mostrar equips incomplets --> ✖
 *      · Afegir puntuació --> ✖
 *
 *
 *  TODO: FLASH MESSAGES
 */


final class MySQLUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    //==========================================================================================
    // USER RELATED QUERIES
    //==========================================================================================


    public function createUser(User $user): void
    {

        // Mirem si l'usuari està creat prèviament.
        if($this->exists($user)){
            print_r("User already created" . "\n\n");
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
        SELECT * FROM users WHERE email = :email LIMIT 1
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $row;
        }

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

    private function exists(User $u)
    {
        $user = $this->getUserByEmail($u->email());

        // Mirem si l'usuari està a la BBDD.
        if($user == null){
            echo "User not created"; //TODO: Flash message.
            return false;
        }

        return true;
    }



    //==========================================================================================
    // TEAM RELATED QUERIES
    //==========================================================================================



    public function getTeamByID(int $id)
    {

    }

    /**
     * Function that creates a team given 2 users.
     * @param User $u1
     * @param User $u2
     * @return void
    */
    public function createTeam(String $teamName, User $u1, User $u2){

        // Mirem si l'usuari està registrat en un equip.
        if($this->hasTeam($u1) || $this->hasTeam($u2)){
            print_r("USER ALREADY REGISTERED \n\n"); //TODO: Flash message.
            return;
        }

        // Guardem als equips
        $user1 = $this->getUserByEmail($u1->email());
        $user2 = $this->getUserByEmail($u2->email());

        // Creem l'equip.
        $query = <<<'QUERY'
            INSERT INTO teams (team_name, count, user_id_1, user_id_2, score) VALUES
            (:teamName, 2, :email1, :email2, 0);
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Posem els paràmetres.
        $id1 = $user1->id;
        $id2 = $user2->id;
        $statement->bindParam(':teamName', $teamName, PDO::PARAM_STR);
        $statement->bindParam(':email1', $id1, PDO::PARAM_STR);
        $statement->bindParam(':email2', $id2, PDO::PARAM_STR);

        $statement->execute();

    }

    public function createSoloTeam(String $teamName, User $u1): void
    {

        // Mirem si l'usuari està registrat en un equip.
        if($this->hasTeam($u1)){
            print_r("USER ALREADY REGISTERED \n\n"); //TODO: Flash message.
            return;
        }

        // Creem l'equip.
        $query = <<<'QUERY'
            INSERT INTO teams (team_name, count, user_id_1, user_id_2, score) VALUES
            (:teamName, 1, (SELECT users.id FROM users WHERE users.email = :email LIMIT 1), null, 0);
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Posem els paràmetres.
        $email1 = $u1->email();
        $statement->bindParam(':teamName', $teamName, PDO::PARAM_STR);
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
            echo "User not created (team) \n"; //TODO: Flash message.
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

    public function addToTeamByID(int $teamId, User $u){

        // Mirem que els usuaris existeixin.
        if($this->exists($u) && !$this->hasTeam($u)){

            // Busquem l'usuari que té equip a la BBDD per a aconseguir l'ID.
            $user = $this->getUserByEmail($u->email());

            $query = <<<'QUERY'
                UPDATE teams
                SET user_id_2 = :idNew
                WHERE team_id = :idTeam;
            QUERY;

            $statement = $this->databaseConnection->prepare($query);

            // Busquem la id de l'usuari.
            $id = $user->id;

            $statement->bindParam('idNew', $id, PDO::PARAM_INT);
            $statement->bindParam('idTeam', $teamId, PDO::PARAM_INT);

            $statement->execute();


        }elseif (!$this->hasTeam($u)){
            print_r("The user doesn't have a team"); //TODO: flash message
            return;
        }

    }

    /**
     * Funció que afegeix un usuari a un equip ja existent.
     * @param User $oldUser
     * @param User $newUser
     * @return void
    */
    public function addToTeam(User $oldUser, User $newUser){

        // Mirem que els usuaris existeixin.
        if($this->exists($oldUser) && $this->exists($newUser)){

            // Mirem que el primer usuari tingui equip i que el segon no.
            if($this->hasTeam($oldUser) && !$this->hasTeam($newUser)){

                // Mirem si l'usuari està a la BBDD.
                if(!$this->exists($oldUser) || !$this->exists($newUser)){
                    print_r("USERS NOT CREATED, CANNOT JOIN TEAM"); //TODO: flash message
                    return;
                }

                // Busquem l'usuari que té equip a la BBDD per a aconseguir l'ID.
                $userNew = $this->getUserByEmail($newUser->email());

                $query = <<<'QUERY'
                    UPDATE teams
                    SET user_id_2 = :idNew
                    WHERE team_id = :idTeam;
                QUERY;

                $statement = $this->databaseConnection->prepare($query);

                // Busquem la id de l'usuari.
                $idNew = $userNew->id;

                // Guardem la id d'equip de l'usuari que ha té equip.
                $idTeam = $this->getTeamID($oldUser);

                $statement->bindParam('idNew', $idNew, PDO::PARAM_INT);
                $statement->bindParam('idTeam', $idTeam['team_id'], PDO::PARAM_INT);

                $statement->execute();


            }elseif ($this->hasTeam($newUser)){
                print_r("The new user can't be added to the new team, it's already in one"); //TODO: flash message
                return;
            }
        }
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


    public function getIncompleteTeams()
    {
        // Fem la query
        $query = <<<'QUERY'
            SELECT * FROM teams WHERE user_id_1 IS NULL OR user_id_2 IS NULL;
        QUERY;

        // Preparem la query
        $statement = $this->databaseConnection->prepare($query);

        // Executem la query
        $statement->execute();

        // Retornem els resultats de la query
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScore(User $u)
    {
        // Fem la query
        $query = <<<'QUERY'
            SELECT score FROM teams WHERE user_id_1 = :id OR user_id_2 = :id;
        QUERY;

        // Preparem la query
        $statement = $this->databaseConnection->prepare($query);

        $user = $this->getUserByEmail($u->email());
        $id = $user->id;

        $statement->bindParam('id', $id, PDO::PARAM_INT);
        $statement->bindParam('id', $id, PDO::PARAM_INT);
        // Executem la query
        $statement->execute();

        // Retornem els resultats de la query
        $count = $statement->fetch(PDO::FETCH_ASSOC);
        return $count['score'];

    }

    public function setScore(User $u, int $score)
    {
        // Fem la query
        $query = <<<'QUERY'
            UPDATE teams
            SET score = :score
            WHERE user_id_1 = :id;
        QUERY;

        // Preparem la query
        $statement = $this->databaseConnection->prepare($query);

        $user = $this->getUserByEmail($u->email());
        $id = $user->id;

        $statement->bindParam('id', $id, PDO::PARAM_INT);
        $statement->bindParam('score', $score, PDO::PARAM_INT);

        // Executem la query
        $statement->execute();


    }
}