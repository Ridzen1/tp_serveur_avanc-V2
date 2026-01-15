<?php
namespace toubilib\core\application\ports\spi;

interface PraticienServiceInterface {
    public function getPraticienById(string $id): array;
}