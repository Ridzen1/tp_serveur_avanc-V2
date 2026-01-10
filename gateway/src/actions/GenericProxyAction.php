<?php
declare(strict_types=1);

namespace Gateway\Actions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class GenericProxyAction
{
    private Client $httpClient;
    private LoggerInterface $logger;

    public function __construct(Client $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();
        
        $uri = $path . ($query ? '?' . $query : '');

        try {
            $this->logger->info("Gateway: Forwarding {$method} request to {$uri}");

            $apiResponse = $this->httpClient->request($method, $uri, [
                'body' => $request->getBody(),
                'headers' => $request->getHeaders()
            ]);

            $this->logger->info('Gateway: Received response from API', [
                'status' => $apiResponse->getStatusCode()
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
            $this->logger->error('Gateway: API Client error', [
                'status' => $status,
                'message' => $e->getMessage()
            ]);

            switch ($status) {
                case 400: throw new \Slim\Exception\HttpBadRequestException($request, $e->getMessage());
                case 401: throw new \Slim\Exception\HttpUnauthorizedException($request, $e->getMessage());
                case 403: throw new \Slim\Exception\HttpForbiddenException($request, $e->getMessage());
                case 404: throw new \Slim\Exception\HttpNotFoundException($request, $e->getMessage());
                case 405: throw new \Slim\Exception\HttpMethodNotAllowedException($request, $e->getMessage());
                default: throw new \Slim\Exception\HttpInternalServerErrorException($request, $e->getMessage());
            }

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->logger->error('Gateway: API Server error', [
                'status' => $e->getResponse()->getStatusCode(),
                'message' => $e->getMessage()
            ]);
            throw new \Slim\Exception\HttpInternalServerErrorException($request, "Erreur interne de l'API backend");

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->logger->error('Gateway: Connection error', ['error' => $e->getMessage()]);
            throw new \Slim\Exception\HttpInternalServerErrorException($request, "API non accessible ou délai dépassé");

        } catch (\Exception $e) {
            $this->logger->error('Gateway: Unexpected error', ['error' => $e->getMessage()]);
            throw new \Slim\Exception\HttpInternalServerErrorException($request, $e->getMessage());
        }
    }
}
