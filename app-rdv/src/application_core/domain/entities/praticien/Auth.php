<?php

namespace toubilib\core\domain\entities\praticien;
class Auth {
    private string $id;
    private string $email;
    private string $passwordHash;
    private string $role;

    public function __construct(string $id, string $email, string $passwordHash, string $role)
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

    public function getRole(): string
    {
        return $this->role;
    }
}