<?php
return [
    'db.praticien.host' => 'toubiprat.db',
    'db.praticien.port' => '5432',
    'db.praticien.name' => 'toubiprat',
    'db.praticien.user' => 'toubiprat',
    'db.praticien.pass' => 'toubiprat',

    'db.auth.host' => 'toubiauth.db',
    'db.auth.port' => '5432',
    'db.auth.name' => 'toubiauth',
    'db.auth.user' => 'toubiauth',
    'db.auth.pass' => 'toubiauth',

    'db.patient.host' => 'toubipat.db',
    'db.patient.port' => '5432',
    'db.patient.name' => 'toubipat',
    'db.patient.user' => 'toubipat',
    'db.patient.pass' => 'toubipat',

    'db.rdv.host' => 'toubirdv.db',
    'db.rdv.port' => '5432',
    'db.rdv.name' => 'toubirdv',
    'db.rdv.user' => 'toubirdv',
    'db.rdv.pass' => 'toubirdv',

    'displayErrorDetails' => true,
    'logError' => true,
    'logErrorDetails' => true,

    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'default_secret_key_change_me_in_production'
    ],
];
