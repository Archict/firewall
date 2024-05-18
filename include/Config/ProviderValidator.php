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
use Archict\Firewall\Config\Exception\EmptyProviderClassNameException;
use Archict\Firewall\Config\Exception\EmptyProviderNameException;
use Archict\Firewall\Config\Exception\FirewallException;
use Archict\Firewall\UserProvider;
use ReflectionClass;

/**
 * @internal
 */
final class ProviderValidator
{
    /**
     * @param class-string $class_name
     * @throws FirewallException
     */
    public function validate(string $name, string $class_name): void
    {
        $name       = trim($name);
        $class_name = trim($class_name);

        if (trim($name) === '') {
            throw new EmptyProviderNameException();
        }

        if (trim($class_name) === '') {
            throw new EmptyProviderClassNameException($name);
        }

        if (!class_exists($class_name)) {
            throw new ClassNotFoundException($class_name);
        }

        $reflection = new ReflectionClass($class_name);
        if (!$reflection->implementsInterface(UserProvider::class)) {
            throw new ClassMustImplementInterfaceException($class_name, UserProvider::class);
        }

        // Provider is valid
    }
}
