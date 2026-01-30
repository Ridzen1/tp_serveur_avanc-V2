<?php
declare(strict_types=1);

namespace Gateway\Actions;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GenericProxyAction
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();
        
        $uri = $path . ($query ? '?' . $query : '');

        $httpClient = $this->getClientForPath($path);

        try {
            $apiResponse = $httpClient->request($method, $uri, [
                'body' => $request->getBody(),
                'headers' => $request->getHeaders()
            ]);

            $response->getBody()->write($apiResponse->getBody()->getContents());

            $newResponse = $response->withStatus($apiResponse->getStatusCode());
            foreach ($apiResponse->getHeaders() as $name => $values) {
                if (!in_array(strtolower($name), ['content-length', 'transfer-encoding', 'connection'])) {
                    $newResponse = $newResponse->withHeader($name, $values);
                }
            }

            return $newResponse;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Pour les erreurs client (4xx), retourner la réponse du backend
            $apiResponse = $e->getResponse();
            $response->getBody()->write($apiResponse->getBody()->getContents());
            
            $newResponse = $response->withStatus($apiResponse->getStatusCode());
            foreach ($apiResponse->getHeaders() as $name => $values) {
                if (!in_array(strtolower($name), ['content-length', 'transfer-encoding', 'connection'])) {
                    $newResponse = $newResponse->withHeader($name, $values);
                }
            }
            
            return $newResponse;

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new \Slim\Exception\HttpInternalServerErrorException($request, "Erreur interne de l'API backend");

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new \Slim\Exception\HttpInternalServerErrorException($request, "API non accessible ou délai dépassé");

        } catch (\Exception $e) {
            throw new \Slim\Exception\HttpInternalServerErrorException($request, $e->getMessage());
        }
    }
    
    private function getClientForPath(string $path): Client
    {
        if (str_starts_with($path, '/auth') || str_starts_with($path, '/tokens')) {
            return $this->container->get('client.auth');
        }
        if (str_starts_with($path, '/praticiens')) {
            // Les routes d'agenda et de rdvs d'un praticien sont gérées par le service RDV
            if (str_contains($path, '/rdvs') || str_contains($path, '/agenda')) {
                return $this->container->get('client.rdv');
            }
            // Les autres routes praticiens sont gérées par le service praticiens
            return $this->container->get('client.praticiens');
        }
        if (str_starts_with($path, '/rdvs')) {
            return $this->container->get('client.rdv');
        }
        return $this->container->get('toubilib.client');
    }
}
