<?php

require '../vendor/autoload.php';

use toubilib\core\application\usecases\ServiceRdv;
use toubilib\infra\repositories\PDORdvRepository;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\core\application\dto\InputRendezVousDTO;

// Create PDOs
$pdoPraticien = new PDO(
    "pgsql:host=toubiprat.db;port=5432;dbname=toubiprat",
    'toubiprat',
    'toubiprat'
);

$pdoPatient = new PDO(
    "pgsql:host=toubipat.db;port=5432;dbname=toubipat",
    'toubipat',
    'toubipat'
);

$pdoRdv = new PDO(
    "pgsql:host=toubirdv.db;port=5432;dbname=toubirdv",
    'toubirdv',
    'toubirdv'
);

// Create repositories
$praticienRepo = new PDOPraticienRepository($pdoPraticien);
$patientRepo = new PDOPatientRepository($pdoPatient);
$rdvRepo = new PDORdvRepository($pdoRdv);

// Create service
$serviceRdv = new ServiceRdv($rdvRepo, $praticienRepo, $patientRepo);

// Fetch sample data
$praticiens = $praticienRepo->findAll();
if (empty($praticiens)) {
    echo "No praticiens found in DB. Please seed data.\n";
    exit(1);
}
$praticien = $praticiens[0];
$praticienId = $praticien->getId();
$motifs = $praticien->getMotifsVisite();
if (empty($motifs)) {
    echo "No motifs for praticien $praticienId.\n";
    exit(1);
}
$motifId = (string)$motifs[0]->getId();

// Fetch a patient ID directly
$stmt = $pdoPatient->query('SELECT id FROM patient LIMIT 1');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "No patients found in DB.\n";
    exit(1);
}
$patientId = $row['id'];

// Test cases
$duree = 30;

echo "=== Test Valid RDV ===\n";
$dateDebut = '2023-10-02 09:00:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "SUCCESS: RDV created.\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

echo "\n=== Test Invalid Praticien ===\n";
$dateDebut = '2023-10-02 09:30:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: 00000000-0000-0000-0000-000000000000, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO('00000000-0000-0000-0000-000000000000', $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Invalid Patient ===\n";
$dateDebut = '2023-10-02 09:30:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: 00000000-0000-0000-0000-000000000000, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, '00000000-0000-0000-0000-000000000000', $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Invalid Motif ===\n";
$dateDebut = '2023-10-02 09:30:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin, Motif: 999\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, '999', $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Invalid Day (Weekend) ===\n";
$dateDebut = '2023-10-07 09:00:00'; // Saturday
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Invalid Hour (Too Early) ===\n";
$dateDebut = '2023-10-02 07:00:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Invalid Hour (Too Late) ===\n";
$dateDebut = '2023-10-02 20:00:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Create Test RDV for Detailed Overlap Tests (10:00-11:00) ===\n";
$dateDebut = '2023-10-02 10:00:00';
$dateFin = '2023-10-02 11:00:00';
echo "Creating test RDV Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, 60);
    $serviceRdv->creerRendezVous($dto);
    echo "Test RDV created.\n";
} catch (Exception $e) {
    echo "Failed to create test RDV: " . $e->getMessage() . "\n";
}

echo "\n=== Test Overlap Start (9:45-10:15) ===\n";
$dateDebut = '2023-10-02 09:45:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Overlap Exact (10:00-11:00) ===\n";
$dateDebut = '2023-10-02 10:00:00';
$dateFin = '2023-10-02 11:00:00';
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, 60);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Overlap Middle (10:15-10:45) ===\n";
$dateDebut = '2023-10-02 10:15:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\n=== Test Overlap End (10:45-11:15) ===\n";
$dateDebut = '2023-10-02 10:45:00';
$dateFin = date('Y-m-d H:i:s', strtotime($dateDebut) + $duree * 60);
echo "Testing Praticien ID: $praticienId, Patient ID: $patientId, Start: $dateDebut, End: $dateFin\n";
try {
    $dto = new InputRendezVousDTO($praticienId, $patientId, $dateDebut, $motifId, $duree);
    $serviceRdv->creerRendezVous($dto);
    echo "FAILED: Should have thrown exception.\n";
} catch (Exception $e) {
    echo "SUCCESS: " . $e->getMessage() . "\n";
}

echo "\nTests completed. Check DB for inserted RDV.\n";
