<?php

declare(strict_types=1);

namespace Archict\Firewall;

use Archict\Brick\Service;
use Archict\Firewall\Config\FirewallConfiguration;

/**
 * This service doesn't need to be used outside of this Brick.
 * But it needs to be loaded with a configuration file and event listeners.
 * @internal
 */
#[Service(FirewallConfiguration::class)]
final readonly class Firewall
{
    public function __construct(
        public FirewallConfiguration $configuration,
    ) {
    }
}
