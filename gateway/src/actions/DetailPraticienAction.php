<?php
declare(strict_types=1);

namespace Gateway\Actions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class DetailPraticienAction
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
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = $args['id'];
        try {
            $uri = "/praticiens/{$id}";

            $this->logger->info("Gateway: Forwarding request to {$uri}");

            $apiResponse = $this->httpClient->get($uri);

            $this->logger->info('Gateway: Received response from API', [
                'status' => $apiResponse->getStatusCode()
            ]);

            $response->getBody()->write($apiResponse->getBody()->getContents());

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($apiResponse->getStatusCode());

        } catch (\Exception $e) {
            $this->logger->error('Gateway: Error forwarding request', [
                'error' => $e->getMessage()
            ]);

            $response->getBody()->write(json_encode([
                'error' => 'Gateway Error',
                'message' => 'Unable to reach backend API'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(502);
        }
    }
}
