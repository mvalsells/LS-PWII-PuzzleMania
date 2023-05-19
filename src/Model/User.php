<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use DateTime;
use JsonSerializable;

class User implements JsonSerializable
{
    //------------------------------------------------------------------------------------------
    // VARIABLES
    //------------------------------------------------------------------------------------------

    private int $id;
    private string $email;
    private string $password;
    private string $profilePicturePath;
    private Datetime $createdAt;
    private Datetime $updatedAt;


    //------------------------------------------------------------------------------------------
    // CONSTRUCTORS
    //------------------------------------------------------------------------------------------

    public function __construct () {
    }

    //------------------------------------------------------------------------------------------
    // OTHER METHODS
    //------------------------------------------------------------------------------------------

    /**
     * Function called when encoded with json_encode
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function hasPicture(): bool
    {
        if (isset($this->profilePicturePath)) {
            return true;
        }
        return false;
    }

    //------------------------------------------------------------------------------------------
    // GETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return explode('@', $this->email)[0];;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getProfilePicturePath(): string
    {
        return $this->profilePicturePath;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    //------------------------------------------------------------------------------------------
    // SETTERS
    //------------------------------------------------------------------------------------------

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param string $profilePicturePath
     */
    public function setProfilePicturePath(string $profilePicturePath): void
    {
        $this->profilePicturePath = $profilePicturePath;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }


}
