<?php

declare(strict_types=1);

namespace Archict\Firewall;

use Archict\Brick\Service;
use Archict\Firewall\Config\AccessControlValidator;
use Archict\Firewall\Config\ConfigValidator;
use Archict\Firewall\Config\Exception\FirewallException;
use Archict\Firewall\Config\FirewallConfiguration;
use Archict\Firewall\Config\ProviderValidator;

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
        public FirewallConfiguration $configuration,
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
}
