<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\ports\api\ServiceAuthInterface;

class AuthMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    private ServiceAuthInterface $serviceAuth;

    public function __construct(ServiceAuthInterface $serviceAuth)
    {
        $this->serviceAuth = $serviceAuth;
    }

    public function __invoke(ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
         try {
            $authDto = $this->authProvider->getSignedInUser($token);
        } catch (AuthProviderInvalidAccessToken $e) {
            throw new HttpUnauthorizedException($request, 'Invalid access token', $e);
        } catch (AuthProviderExpiredAccessToken $e) {
            throw new HttpUnauthorizedException($request, 'Expired access token', $e);
        }

    }
}