<?php
/* Todo
 *
 * (The MIT license)
 * Copyright (c) 2022 Jigius
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated * documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace Todo;

use Throwable;

/**
 * Suppresses all errors in the time of data loading from a file
 */
final class WithErrorSuppressedIgnoreFile implements IgnoreFileInterface
{
    /**
     * @var IgnoreFileInterface
     */
    private $origin;
    
    /**
     * Cntr
     */
    public function __construct(IgnoreFileInterface $i)
    {
        $this->origin = $i;
    }
    
    /**
     * @inheritDoc
     */
    public function loadedFrom(string $pathfile): IgnoreFileInterface
    {
        try {
            $that = $this->blueprinted();
            $that->origin = $this->origin->loadedFrom($pathfile);
            $ret = $that;
        } catch (Throwable $ex) {
            $ret = $this;
        }
        return $ret;
    }
    
    /**
     * @inheritDoc
     */
    public function known(string $hash): bool
    {
        return $this->origin->known($hash);
    }
    
    /**
     * @return $this
     */
    public function blueprinted(): self
    {
        return new self($this->origin);
    }
}
