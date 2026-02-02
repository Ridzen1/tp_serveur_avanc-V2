<?php 

namespace toubilib\core\application\dto;

class AuthDTO {
    public string $id;
    public string $email;
    public string $role;

    public function __construct(string $id, string $email, string $role) {
        $this->id = $id;
        $this->email = $email;
        $this->role = $role;
    }
}