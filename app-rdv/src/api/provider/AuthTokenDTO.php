<?php

namespace toubilib\api\provider;

use toubilib\core\application\dto\AuthDTO;

/**
 * DTO d'authentification contenant le profil utilisateur et les tokens JWT
 */
class AuthTokenDTO 
{
    public AuthDTO $user;
    public string $accessToken;
    public string $refreshToken;

    public function __construct(AuthDTO $user, string $accessToken, string $refreshToken) 
    {
        $this->user = $user;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    /**
     * Convertit le DTO en tableau pour la réponse JSON
     */
    public function toArray(): array 
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'role' => $this->user->role
            ],
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken
        ];
    }
}