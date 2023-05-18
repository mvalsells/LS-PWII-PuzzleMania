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

    //==========================================================================================
    // USER RELATED QUERIES
    //==========================================================================================


    public function createUser(User $user): void
    {
        // Build the SQL query
        $query = <<<'QUERY'
            INSERT INTO users(email, password, createdAt, updatedAt)
            VALUES(:email, :password, :createdAt, :updatedAt)
        QUERY;

        // Extract the variables we want to upload to database
        $email = $user->getEmail();
        $password = $user->getPassword();
        $createdAt = $user->getCreatedAt()->format(self::DATE_FORMAT);
        $updatedAt = $user->getUpdatedAt()->format(self::DATE_FORMAT);

        $statement = $this->databaseConnection->prepare($query);

        // Introduce variables in statement query
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('createdAt', $createdAt, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        // Execute query
        $statement->execute();
    }

    public function getUserByEmail(string $email): ?User
    {
        // Build the SQL query
        $query = <<<'QUERY'
        SELECT * FROM users WHERE email = :email LIMIT 1
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Introduce 'email' variable in statement query
        $statement->bindParam('email', $email, PDO::PARAM_STR);

        // Execute the query
        $statement->execute();

        // Check if any user has this email
        $count = $statement->rowCount();
        if ($count > 0) {
            // Extract the first user (theoretically, only one user is linked to that email)
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $this->createUserVariable($row);
        }
        // Return null
        return null;
    }

    public function updateProfilePicture(int $id, string $profilePicturePath): void
    {
        // Build the SQL query
        $query = <<<'QUERY'
        UPDATE `users`
        SET `profilePicturePath` = :profilePicturePath,
            `updatedAt` = NOW()
        WHERE `id` = :id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Introduce variables in statement query
        $statement->bindParam('id', $id, PDO::PARAM_INT);
        $statement->bindParam('profilePicturePath', $profilePicturePath, PDO::PARAM_STR);

        // Execute the query
        $statement->execute();
    }

    public function getUserById(int $id): ?User
    {
        // Build the SQL query
        $query = <<<'QUERY'
        SELECT * FROM users WHERE id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Introduce 'id' variable in statement query
        $statement->bindParam('id', $id, PDO::PARAM_INT);

        // Execute the query
        $statement->execute();

        // Check if any user has this id
        $count = $statement->rowCount();
        if ($count > 0) {
            // Extract the first user (theoretically, only one user is linked to that id)
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $this->createUserVariable($row);
        }
        return null;
    }

    public function getAllUsers(): array
    {
        // Build the SQL query
        $query = <<<'QUERY'
        SELECT * FROM users
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        // Execute the query
        $statement->execute();

        $users = [];

        // Count the number of users
        $count = $statement->rowCount();

        // Fetch all the users and add them to array
        for ($i = 0; $i < $count; $i++) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            $users[] = $this->createUserVariable($row);
        }

        return $users;
    }


    //==========================================================================================
    // OTHER FUNCTIONS
    //==========================================================================================

    private function createUserVariable($row): User
    {
        // Create user variable
        $user = new User();
        $user->setId($row->id);
        $user->setEmail($row->email);
        $user->setPassword($row->password);
        if ($row->profilePicturePath != null) {
            $user->setProfilePicturePath($row->profilePicturePath);
        }
        return $user;
    }

}
