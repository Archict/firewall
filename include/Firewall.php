<?php

declare(strict_types=1);

namespace Archict\Firewall;

use Archict\Brick\Service;

/**
 * This service doesn't need to be used outside of this Brick.
 * But it needs to be loaded with a configuration file and event listeners.
 * @internal
 */
#[Service]
final readonly class Firewall
{
}
