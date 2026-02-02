<?php

namespace toubilib\api\provider;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\api\provider\AuthTokenDTO;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;

class AuthProvider 
{
    private ServiceAuth $serviceAuth;
    private AuthRepositoryInterface $authRepository;
    private string $jwtSecret;
    
    public function __construct(ServiceAuth $serviceAuth, AuthRepositoryInterface $authRepository, string $jwtSecret) 
    {
        $this->serviceAuth = $serviceAuth;
        $this->authRepository = $authRepository;
        $this->jwtSecret = $jwtSecret;
    }


    public function signin(string $email, string $password): ?AuthTokenDTO 
    {
        $authDTO = $this->serviceAuth->authentication($email, $password);
        
        if ($authDTO === null) {
            return null;
        }

        $accessToken = $this->generateAccessToken($authDTO);
        $refreshToken = $this->generateRefreshToken($authDTO);

        return new AuthTokenDTO(
            $authDTO,
            $accessToken,
            $refreshToken
        );
    }


    public function refresh(string $refreshToken): array
    {
        try {
            // Décoder et valider le refresh token
            $decoded = JWT::decode($refreshToken, new Key($this->jwtSecret, 'HS256'));
            
            // Vérifier que c'est bien un refresh token
            if (!isset($decoded->type) || $decoded->type !== 'refresh_token') {
                throw new \Exception('Invalid token type');
            }
            
            // Récupérer l'utilisateur depuis la base de données
            $user = $this->authRepository->findUserById($decoded->sub);
            
            if ($user === null) {
                throw new \Exception('User not found');
            }
            
            // Créer un AuthDTO avec les données complètes
            $authDTO = new AuthDTO(
                $user->getId(),
                $user->getEmail(),
                $user->getRole()
            );
            
            // Générer de nouveaux tokens
            $newAccessToken = $this->generateAccessToken($authDTO);
            $newRefreshToken = $this->generateRefreshToken($authDTO);
            
            return [
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken
            ];
            
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired refresh token');
        }
    }


    private function generateAccessToken(AuthDTO $authDTO): string 
    {
        $payload = [
            'iss' => 'toubilib',
            'sub' => $authDTO->id,
            'email' => $authDTO->email,
            'role' => $authDTO->role,
            'iat' => time(),
            'exp' => time() + (15 * 60),
            'type' => 'access_token'
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }


    private function generateRefreshToken(AuthDTO $authDTO): string 
    {
        $payload = [
            'iss' => 'toubilib',
            'sub' => $authDTO->id,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60),
            'type' => 'refresh_token'
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * Valide un token JWT (access_token ou refresh_token)
     * 
     * @param string $token Le token à valider
     * @return array Informations sur la validité du token
     * @throws \Exception Si le token est invalide
     */
    public function validateToken(string $token): array
    {
        try {
            // Décoder et valider le token
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Vérifier que le token n'est pas expiré (fait automatiquement par JWT::decode)
            // Vérifier l'émetteur
            if (!isset($decoded->iss) || $decoded->iss !== 'toubilib') {
                throw new \Exception('Invalid token issuer');
            }
            
            // Vérifier le type de token
            if (!isset($decoded->type) || !in_array($decoded->type, ['access_token', 'refresh_token'])) {
                throw new \Exception('Invalid token type');
            }
            
            // Retourner les informations du token
            return [
                'valid' => true,
                'user_id' => $decoded->sub,
                'email' => $decoded->email ?? null,
                'role' => $decoded->role ?? null,
                'type' => $decoded->type,
                'expires_at' => $decoded->exp
            ];
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new \Exception('Token expired');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new \Exception('Invalid token signature');
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }
}