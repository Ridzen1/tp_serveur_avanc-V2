<?php

namespace toubilib\core\domain\entities;


class User 
{
    private string $id;
    private string $email;
    private string $passwordHash;
    private int $role;

    public function __construct(string $id, string $email, string $passwordHash, int $role) 
    {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
    }

    public function getId(): string 
    {
        return $this->id;
    }

    public function getEmail(): string 
    {
        return $this->email;
    }

    public function getPasswordHash(): string 
    {
        return $this->passwordHash;
    }

    public function getRole(): int 
    {
        return $this->role;
    }

    public function setPasswordHash(string $passwordHash): void 
    {
        $this->passwordHash = $passwordHash;
    }


    public function verifyPassword(string $password): bool 
    {
        return password_verify($password, $this->passwordHash);
    }
}