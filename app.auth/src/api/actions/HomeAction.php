<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class HomeAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'message' => 'Bienvenue sur l\'API Toubilib',
            'version' => '1.0.0',
            'endpoints' => [
                'GET /praticiens?ville={ville}&specialite={specialite}' => 'Lister les praticiens (Filtres optionnels : ville, specialite)',                
                'GET /praticiens/{id}' => 'Afficher les détails d\'un praticien',
                'GET /praticiens/{id}/creneaux' => 'Lister les créneaux occupés d\'un praticien',
                "GET /praticiens/{id}/agenda?date_debut=YYYY-MM-DD%2000:00:00&date_fin=YYYY-MM-DD%2023:59:59" => "Consulter l'agenda d'un praticien sur une période donnée (préciser les heures pour inclure toute la journée)",
                
                'GET /rdvs/{id}' => 'Consulter un rendez-vous',
                // http://localhost:6080/praticiens/4305f5e9-be5a-4ccf-8792-7e07d7017363/agenda?date_debut=2025-12-04%2000:00:00&date_fin=2025-12-04%2023:59:59 url de test
                'POST /rdvs' => 'Créer un rendez-vous',
                'POST /rdvs/{id}/annuler' => 'Annuler un rendez-vous (Attention: route modifiée)',
                'POST /rdvs/{id}/honorer' => 'Marquer un rendez-vous comme honoré',
                'POST /rdvs/{id}/non-honore' => 'Marquer un rendez-vous comme non honoré',
                'POST /praticiens/{id}/indisponibilites' => 'Ajouter une indisponibilité pour un praticien',

                'POST /inscription' => 'Inscrire un nouveau patient',
                'GET /patients/{id}/consultations' => 'Obtenir l\'historique des consultations d\'un patient',
                
                'POST /auth/login' => 'Authentification simple (email + password)',
                'POST /auth/signin' => 'Authentification avec JWT tokens (email + password) - retourne access_token et refresh_token'
            ]
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
