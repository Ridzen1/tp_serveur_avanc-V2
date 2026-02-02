<?php
require_once __DIR__ . '/../vendor/autoload.php';

use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\core\application\usecases\ServicePraticien;

try {
    $pdo = new \PDO('pgsql:host=toubiprat.db;dbname=toubiprat', 'toubiprat', 'toubiprat');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    $repository = new PDOPraticienRepository($pdo);
    $service = new ServicePraticien($repository);

    $praticiens = $service->listerPraticiens();

    echo "=== LISTE DES PRATICIENS ===\n\n";

    if (empty($praticiens)) {
        echo "Aucun praticien trouvé.\n";
    } else {
        foreach ($praticiens as $index => $praticien) {
            echo "Praticien #" . ($index + 1) . "\n";
            echo "------------------------\n";
            echo "Nom: " . $praticien->nom . "\n";
            echo "Prénom: " . $praticien->prenom . "\n";
            echo "Ville: " . $praticien->ville . "\n";
            echo "Spécialité: " . $praticien->specialite . "\n";
            echo "Email: " . $praticien->email . "\n";
            echo "\n";
        }

        echo "Total: " . count($praticiens) . " praticien(s) trouvé(s).\n";
    }

} catch (\PDOException $e) {
    echo "Erreur de base de données: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
