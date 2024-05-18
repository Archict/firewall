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
use Archict\Firewall\Config\Exception\FirewallException;
use Archict\Firewall\Config\Exception\MissingBehaviorTagException;
use Archict\Firewall\Config\Exception\MissingRolesException;
use Archict\Firewall\Config\Exception\MissingRoleTagException;
use Archict\Firewall\Config\Exception\InvalidHttpCodeException;
use Archict\Firewall\Config\Exception\UnknownProviderException;
use Archict\Firewall\UserAccessChecker;
use ReflectionClass;

/**
 * @internal
 */
final class AccessControlValidator
{
    /**
     * @param array<string, class-string> $providers
     * @throws FirewallException
     */
    public function validate(AccessControlRepresentation $representation, array $providers): void
    {
        if ($representation->path === '') {
            throw new EmptyRulePathException();
        }

        if ($representation->provider !== null) {
            if (!isset($providers[$representation->provider])) {
                throw new UnknownProviderException($representation->provider);
            }

            if ($representation->roles === null) {
                throw new MissingRoleTagException();
            }

            if ($representation->roles === []) {
                throw new MissingRolesException();
            }

            $this->validateBehavior($representation);

            return; // Representation is valid
        }

        if (!class_exists($representation->checker)) {
            throw new ClassNotFoundException($representation->checker);
        }

        $reflection = new ReflectionClass($representation->checker);
        if (!$reflection->implementsInterface(UserAccessChecker::class)) {
            throw new ClassMustImplementInterfaceException($representation->checker, UserAccessChecker::class);
        }

        $this->validateBehavior($representation);

        // Representation is valid
    }

    /**
     * @throws FirewallException
     */
    private function validateBehavior(AccessControlRepresentation $representation): void
    {
        if ($representation->error !== null && $representation->redirect_to === null
            && ($representation->error < 100 || $representation->error >= 600)
        ) {
            throw new InvalidHttpCodeException($representation->error);
        } else if (($representation->error === null && $representation->redirect_to === null)
            || ($representation->error !== null && $representation->redirect_to !== null)
        ) {
            throw new MissingBehaviorTagException();
        }
    }
}
