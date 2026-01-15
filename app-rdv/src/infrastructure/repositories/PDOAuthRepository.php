<?php 

namespace toubilib\infrastructure\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\core\domain\entities\User;

class PDOAuthRepository implements AuthRepositoryInterface 
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo) 
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    }


    public function findUserByEmail(string $email): ?User 
    {
        // sélectionner la colonne password et l'aliaser en password_hash pour correspondre à l'entité User
        $stmt = $this->pdo->prepare('SELECT id, email, password AS password_hash, role FROM public.users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new User(
            $row['id'],
            $row['email'],
            $row['password'],
            $row['role']
        );
    }

    public function saveUser(User $user): void 
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash, role) VALUES (:email, :password_hash, :role)');
        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'role' => $user->getRole()
        ]);
        
    }
}