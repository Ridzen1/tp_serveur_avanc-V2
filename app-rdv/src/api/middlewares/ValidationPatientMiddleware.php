<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;
use toubilib\core\application\dto\InputPatientDTO;
class ValidationPatientMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();

        if (empty($data)) {
            return $this->returnError('Le corps de la requête est vide ou invalide.');
        }

        $rules = v::attribute('nom', v::stringType()->notEmpty()->length(1, 64))
            ->attribute('prenom', v::stringType()->notEmpty()->length(1, 64))
            ->attribute('telephone', v::stringType()->notEmpty()->length(1, 24))
            ->attribute('email', v::optional(v::email()->length(1, 128)))
            ->attribute('adresse', v::optional(v::stringType()))
            ->attribute('code_postal', v::optional(v::stringType()->length(5, 8)))
            ->attribute('ville', v::optional(v::stringType()->length(1, 64)))
            ->attribute('date_naissance', v::optional(v::date('Y-m-d')));

        try {
            $rules->assert((object)$data);

            $dto = new InputPatientDTO(
                $data['nom'],
                $data['prenom'],
                $data['telephone'],
                $data['email'] ?? null,
                $data['adresse'] ?? null,
                $data['code_postal'] ?? null,
                $data['ville'] ?? null,
                $data['date_naissance'] ?? null
            );

            $request = $request->withAttribute('inputPatientDTO', $dto);
            return $handler->handle($request);

        } catch (NestedValidationException $e) {
            $errors = $e->getMessages([
                'nom' => 'Le nom est requis (max 64 car.).',
                'prenom' => 'Le prénom est requis (max 64 car.).',
                'telephone' => 'Le téléphone est requis.',
                'email' => 'Format email invalide.',
                'date_naissance' => 'La date doit être au format YYYY-MM-DD.'
            ]);
            
            return $this->returnError('Données invalides', 400, $errors);
        }
    }

    private function returnError(string $message, int $code = 400, array $details = []): Response
    {
        $response = new SlimResponse();
        $payload = ['status' => 'error', 'message' => $message];
        if (!empty($details)) $payload['details'] = $details;

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
    }
}