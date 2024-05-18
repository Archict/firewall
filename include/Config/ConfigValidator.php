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

use Archict\Firewall\Config\Exception\FirewallException;

/**
 * @internal
 */
final readonly class ConfigValidator
{
    public function __construct(
        private ProviderValidator $provider_validator,
        private AccessControlValidator $access_control_validator,
    ) {
    }

    /**
     * @throws FirewallException
     */
    public function validate(FirewallConfiguration $configuration): void
    {
        foreach ($configuration->providers as $name => $class_name) {
            $this->provider_validator->validate($name, $class_name);
        }

        foreach ($configuration->access_control as $representation) {
            $this->access_control_validator->validate($representation, $configuration->providers);
        }
    }
}
