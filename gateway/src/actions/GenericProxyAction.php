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

            // Copie les headers pertinents de la réponse API vers la réponse Gateway
            $newResponse = $response->withStatus($apiResponse->getStatusCode());
            foreach ($apiResponse->getHeaders() as $name => $values) {
                // Éviter certains headers qui pourraient poser problème
                if (!in_array(strtolower($name), ['content-length', 'transfer-encoding', 'connection'])) {
                    $newResponse = $newResponse->withHeader($name, $values);
                }
            }

            return $newResponse;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $status = $e->getResponse()->getStatusCode();

            switch ($status) {
                case 400: throw new \Slim\Exception\HttpBadRequestException($request, $e->getMessage());
                case 401: throw new \Slim\Exception\HttpUnauthorizedException($request, $e->getMessage());
                case 403: throw new \Slim\Exception\HttpForbiddenException($request, $e->getMessage());
                case 404: throw new \Slim\Exception\HttpNotFoundException($request, $e->getMessage());
                case 405: throw new \Slim\Exception\HttpMethodNotAllowedException($request, $e->getMessage());
                default: throw new \Slim\Exception\HttpInternalServerErrorException($request, $e->getMessage());
            }

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
        // Si l'URL commence par /praticiens
        if (str_starts_with($path, '/praticiens')) {
            if (str_contains($path, '/rdvs')) {
                return $this->container->get('client.rdv');
            }
            return $this->container->get('client.praticiens'); // Vérifie le nom dans ton services.php
        }
        
        // AJOUT EXERCICE 4 : Si l'URL commence par /rdvs
        if (str_starts_with($path, '/rdvs')) {
            return $this->container->get('client.rdv');
        }

        // Par défaut -> Monolithe
        return $this->container->get('toubilib.client');
    }
}
