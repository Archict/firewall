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

use Archict\Firewall\Config\Exception\ClassNotFoundException;
use Archict\Firewall\Config\Exception\EmptyProviderClassNameException;
use Archict\Firewall\Config\Exception\EmptyProviderNameException;
use Archict\Firewall\Config\Exception\ClassMustImplementInterfaceException;
use Archict\Firewall\UserProviderStub;
use PHPUnit\Framework\TestCase;

class ProviderValidatorTest extends TestCase
{
    public function testItThrowsIfNameIsEmpty(): void
    {
        self::expectException(EmptyProviderNameException::class);
        (new ProviderValidator())->validate(' ', UserProviderStub::class);
    }

    public function testItThrowsIfClassNameIsEmpty(): void
    {
        self::expectException(EmptyProviderClassNameException::class);
        (new ProviderValidator())->validate('my_provider', ' '); // @phpstan-ignore-line
    }

    public function testItThrowsIfClassDoesntExists(): void
    {
        self::expectException(ClassNotFoundException::class);
        (new ProviderValidator())->validate('my_provider', 'non-existing-class'); // @phpstan-ignore-line
    }

    public function testItThrowsIfClassNotImplementUserProvider(): void
    {
        self::expectException(ClassMustImplementInterfaceException::class);
        $obj = new class {
        };
        (new ProviderValidator())->validate('my_provider', $obj::class);
    }

    public function testItAcceptAValidProvider(): void
    {
        self::expectNotToPerformAssertions();
        (new ProviderValidator())->validate('my_provider', UserProviderStub::class);
    }
}
