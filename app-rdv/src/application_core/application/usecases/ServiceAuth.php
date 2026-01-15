<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\core\domain\entities\User;

class ServiceAuth 
{
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository) 
    {
        $this->authRepository = $authRepository;
    }

    public function authentication(string $email, string $password): ?AuthDTO 
    {
        $user = $this->authRepository->findUserByEmail($email);
        if ($user && password_verify($password, $user->getPasswordHash())) {
            return new AuthDTO(
                $user->getId(),
                $user->getEmail(),
                $user->getRole()
            );
        }
        return null;
    }
    public function register(string $email, string $password, string $role): AuthDTO 
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $user = new User(uniqid(), $email, $passwordHash, $role);
        $this->authRepository->saveUser($user);
        return new AuthDTO(
            $user->getId(),
            $user->getEmail(),
            $user->getRole()
        );
    }
}