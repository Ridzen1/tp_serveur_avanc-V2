<?php

namespace toubilib\api\provider;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\api\provider\AuthTokenDTO;

class AuthProvider 
{
    private ServiceAuth $serviceAuth;
    private string $jwtSecret;
    
    public function __construct(ServiceAuth $serviceAuth, string $jwtSecret) 
    {
        $this->serviceAuth = $serviceAuth;
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
}