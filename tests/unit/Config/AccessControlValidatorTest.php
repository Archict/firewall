<?php
/**
 * MIT License
 *
 * Copyright (c) 2024-Present Kevin Traini
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Archict\Firewall\Config;

use Archict\Firewall\Config\Exception\ClassMustImplementInterfaceException;
use Archict\Firewall\Config\Exception\ClassNotFoundException;
use Archict\Firewall\Config\Exception\EmptyRulePathException;
use Archict\Firewall\Config\Exception\InvalidHttpCodeException;
use Archict\Firewall\Config\Exception\MissingBehaviorTagException;
use Archict\Firewall\Config\Exception\MissingRolesException;
use Archict\Firewall\Config\Exception\MissingRoleTagException;
use Archict\Firewall\Config\Exception\UnknownProviderException;
use Archict\Firewall\UserAccessCheckerStub;
use Archict\Firewall\UserProviderStub;
use PHPUnit\Framework\TestCase;

class AccessControlValidatorTest extends TestCase
{
    public function testItThrowsIfPathIsEmpty(): void
    {
        self::expectException(EmptyRulePathException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation(''),
            [],
        );
    }

    public function testItThrowsProviderNotExists(): void
    {
        self::expectException(UnknownProviderException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', 'provider'),
            [],
        );
    }

    public function testItThrowsIfRolesAreNotSuppliedWhenUsingFirewallChecker(): void
    {
        self::expectException(MissingRoleTagException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', 'provider'),
            ['provider' => UserProviderStub::class],
        );
    }

    public function testItThrowsIfRolesAreEmptyWhenUsingFirewallChecker(): void
    {
        self::expectException(MissingRolesException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', 'provider', []),
            ['provider' => UserProviderStub::class],
        );
    }

    public function testItThrowsIfErrorCodeIsInvalidWhenUsingFirewallChecker(): void
    {
        self::expectException(InvalidHttpCodeException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', 'provider', ['role'], 50),
            ['provider' => UserProviderStub::class],
        );
    }

    public function testItThrowsIfBehaviorNotSuppliedWhenUsingFirewallChecker(): void
    {
        self::expectException(MissingBehaviorTagException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', 'provider', ['role']),
            ['provider' => UserProviderStub::class],
        );
    }

    public function testItThrowsIfAllBehaviorAreSuppliedWhenUsingFirewallChecker(): void
    {
        self::expectException(MissingBehaviorTagException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', 'provider', ['role'], 404, '/login'),
            ['provider' => UserProviderStub::class],
        );
    }

    public function testItAcceptAValidRepresentationWhenUsingFirewallChecker(): void
    {
        self::expectNotToPerformAssertions();
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', 'provider', ['role'], 404),
            ['provider' => UserProviderStub::class],
        );
    }

    public function testItThrowsIfClassNameNotExistsWhenUsingOwnChecker(): void
    {
        self::expectException(ClassNotFoundException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', null, null, null, null, 'non-existing'), // @phpstan-ignore-line
            [],
        );
    }

    public function testItThrowsIfClassNotImplementUserAccessCheckerWhenUsingOwnChecker(): void
    {
        self::expectException(ClassMustImplementInterfaceException::class);
        $obj = new class {
        };
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', null, null, null, null, $obj::class),
            [],
        );
    }

    public function testItThrowsIfBehaviorIsInvalidWhenUsingOwnChecker(): void
    {
        self::expectException(MissingBehaviorTagException::class);
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', null, null, null, null, UserAccessCheckerStub::class),
            [],
        );
    }

    public function testItAcceptAValidRepresentationWhenUsingOwnChecker(): void
    {
        self::expectNotToPerformAssertions();
        (new AccessControlValidator())->validate(
            new AccessControlRepresentation('path', null, null, null, '/path', UserAccessCheckerStub::class),
            [],
        );
    }
}
