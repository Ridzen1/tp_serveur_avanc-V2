<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\User;

interface AuthRepositoryInterface 
{
    public function findUserByEmail(string $email): ?User;
    public function findUserById(string $id): ?User;
    public function saveUser(User $user): void;
}