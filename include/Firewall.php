<?php

declare(strict_types=1);

namespace Archict\Firewall;

use Archict\Brick\ListeningEvent;
use Archict\Brick\Service;
use Archict\Firewall\Config\AccessControlRepresentation;
use Archict\Firewall\Config\AccessControlValidator;
use Archict\Firewall\Config\ConfigValidator;
use Archict\Firewall\Config\Exception\FirewallException;
use Archict\Firewall\Config\FirewallConfiguration;
use Archict\Firewall\Config\ProviderValidator;
use Archict\Router\Exception\HTTP\HTTPException;
use Archict\Router\HTTPExceptionFactory;
use Archict\Router\Method;
use Archict\Router\Middleware;
use Archict\Router\RouteCollectorEvent;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionException;

/**
 * This service doesn't need to be used outside of this Brick.
 * But it needs to be loaded with a configuration file and event listeners.
 * @internal
 */
#[Service(FirewallConfiguration::class)]
final readonly class Firewall
{
    /**
     * @throws FirewallException
     */
    public function __construct(
        private FirewallConfiguration $configuration,
    ) {
        $this->validateConfiguration();
    }

    /**
     * @throws FirewallException
     */
    private function validateConfiguration(): void
    {
        $validator = new ConfigValidator(
            new ProviderValidator(),
            new AccessControlValidator(),
        );

        $validator->validate($this->configuration);
    }

    /**
     * @throws ReflectionException
     */
    #[ListeningEvent]
    public function addMiddlewares(RouteCollectorEvent $collector): void
    {
        foreach ($this->configuration->access_control as $access_control) {
            $collector->addMiddleware(
                Method::ALL,
                $access_control->path,
                $this->createMiddlewareFromRepresentation($access_control),
            );
        }
    }

    /**
     * @throws ReflectionException
     */
    private function createMiddlewareFromRepresentation(AccessControlRepresentation $representation): Middleware
    {
        if ($representation->checker === FirewallAccessChecker::class) {
            assert(is_string($representation->provider));
            assert(is_array($representation->roles));
            $checker = new FirewallAccessChecker(
                $this->instantiateProvider($representation->provider),
                $representation->roles,
            );
        } else {
            $checker = $this->instantiateChecker($representation->checker);
        }

        $exception = HTTPExceptionFactory::Forbidden();

        return new class($checker, $exception) implements Middleware {
            public function __construct(
                private readonly UserAccessChecker $checker,
                private readonly HTTPException $exception,
            ) {
            }

            public function process(ServerRequestInterface $request): ServerRequestInterface
            {
                if ($this->checker->canUserAccessResource($request)) {
                    return $request;
                }

                throw $this->exception;
            }
        };
    }

    /**
     * @param class-string $class_name
     * @throws ReflectionException
     */
    private function instantiateProvider(string $class_name): UserProvider
    {
        $reflection = new ReflectionClass($class_name);
        $instance   = $reflection->newInstance();
        assert($instance instanceof UserProvider);
        return $instance;
    }

    /**
     * @param class-string $class_name
     * @throws ReflectionException
     */
    private function instantiateChecker(string $class_name): UserAccessChecker
    {
        $reflection = new ReflectionClass($class_name);
        $instance   = $reflection->newInstance();
        assert($instance instanceof UserAccessChecker);
        return $instance;
    }
}
