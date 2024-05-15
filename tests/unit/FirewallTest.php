<?php

declare(strict_types=1);

namespace Archict\Firewall;

use Archict\Core\Core;
use Archict\Core\Services\ServiceManager;
use PHPUnit\Framework\TestCase;

final class FirewallTest extends TestCase
{
    private ServiceManager $service_manager;

    protected function setUp(): void
    {
        $core = Core::build();
        $core->load();
        $this->service_manager = $core->service_manager;
    }

    public function testFirewallServiceIsLoaded(): void
    {
        self::assertTrue($this->service_manager->has(Firewall::class));
        $firewall = $this->service_manager->get(Firewall::class);
        self::assertInstanceOf(Firewall::class, $firewall);
    }
}
