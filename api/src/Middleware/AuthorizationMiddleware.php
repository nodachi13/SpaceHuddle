<?php

namespace App\Middleware;

use App\Data\AuthorisationType;
use App\Domain\Infrastructure\Service\PermissionService;
use App\Domain\Session\Type\SessionRoleType;
use Casbin\Enforcer;
use Casbin\Exceptions\CasbinException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Routing\RouteContext;
use App\Factory\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * Authorization middleware using Casbin.
 */
final class AuthorizationMiddleware
{
    /**
     * @var App
     */
    private App $app;

    /**
     * @var Enforcer
     */
    private Enforcer $enforcer;

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var PermissionService
     */
    private PermissionService $service;

    //private LoggerInterface $errorLogger;

    /**
     * @param App $app The Slim app.
     * @param Enforcer $enforcer Casbin Enforcer class.
     * @param ResponseFactoryInterface $responseFactory The response factory.
     * @param PermissionService $service The permission service.
     * @param LoggerFactory $loggerFactory The response factory
     */
    public function __construct(
        App $app,
        Enforcer $enforcer,
        ResponseFactoryInterface $responseFactory,
        PermissionService $service,
        LoggerFactory $loggerFactory
    ) {
        $this->app = $app;
        $this->enforcer = $enforcer;
        $this->responseFactory = $responseFactory;
        $this->service = $service;
        /*$this->errorLogger = $loggerFactory
            ->addFileHandler("userError.log")
            ->createLogger();*/
    }

    /**
     * Authorization middleware invokable class.
     *
     * @param ServerRequestInterface $request PSR-7 request
     * @param RequestHandlerInterface $handler PSR-15 request handler
     *
     * @return ResponseInterface The response
     * @throws CasbinException
     */
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $action = $request->getMethod();
        if ($action == "OPTIONS") {
            return $handler->handle($request);
        }

        $authorisation = $request->getAttribute('authorisation');
        $bodyData = $request->getParsedBody();

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $urlData = $route->getArguments();
        $urlPattern = $route->getPattern();

        $uri = $request->getUri();
        // Get a URI/path without the base path
        $uriPath = $this->removeBasePath($uri->getPath());
        if (!str_ends_with($uriPath, "/")) {
            $uriPath = $uriPath . "/";
        }

        if ($authorisation) {
            $authorisationType = strtoupper($authorisation->type);
            if ($this->enforcer->enforce(
                $authorisationType,
                SessionRoleType::mapAuthorisationType($authorisationType),
                $uriPath,
                $action
            )) {
                $sessionRole = $this->service->service(
                    $authorisation,
                    $bodyData,
                    $urlData,
                    $urlPattern,
                    $action
                );
                if (!$this->enforcer->enforce($authorisationType, $sessionRole, $uriPath, $action)) {
                    /*$debugOutput = [
                        "status" => "User has no rights for this entity.",
                        "uri" => json_encode($request->getUri()->getPath()),
                        "header" => json_encode($request->getHeaders())
                    ];
                    $this->errorLogger && $this->errorLogger->info(json_encode($debugOutput));*/
                    return $this->responseFactory->createResponse()
                        ->withHeader("Content-Type", "application/json")
                        //->withHeader('Access-Control-Allow-Origin', '*')
                        ->withStatus(
                            StatusCodeInterface::STATUS_FORBIDDEN,
                            "User has no rights for this entity."
                        );
                }
            } elseif (strtoupper($authorisation->type) != strtoupper(AuthorisationType::NONE)) {
                /*$debugOutput = [
                    "status" => "User has no rights for this service.",
                    "uri" => json_encode($request->getUri()->getPath()),
                    "header" => json_encode($request->getHeaders())
                ];
                $this->errorLogger && $this->errorLogger->info(json_encode($debugOutput));*/
                return $this->responseFactory->createResponse()
                    ->withHeader("Content-Type", "application/json")
                    //->withHeader('Access-Control-Allow-Origin', '*')
                    ->withStatus(
                        StatusCodeInterface::STATUS_FORBIDDEN,
                        "User has no rights for this service."
                    );
            } else {
                /*$debugOutput = [
                    "status" => "No valid access token specified.",
                    "uri" => json_encode($request->getUri()->getPath()),
                    "header" => json_encode($request->getHeaders())
                ];
                $this->errorLogger && $this->errorLogger->info(json_encode($debugOutput));*/
                return $this->responseFactory->createResponse()
                    ->withHeader("Content-Type", "application/json")
                    //->withHeader('Access-Control-Allow-Origin', '*')
                    ->withStatus(
                        StatusCodeInterface::STATUS_UNAUTHORIZED,
                        "No valid access token specified."
                    );
            }
        }

        return $handler->handle($request);
    }

    /**
     * Get a URI/path without the base path.
     * @param string $uriPath URI with including the base path.
     * @return string URI without the base path.
     */
    private function removeBasePath(string $uriPath): string
    {
        $basePath = $this->app->getBasePath();

        if (empty($basePath) || !str_starts_with($uriPath, $basePath)) {
            return $uriPath;
        }

        return substr($uriPath, strlen($basePath)) ?: "/";
    }
}
