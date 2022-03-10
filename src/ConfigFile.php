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

use LogicException;
use RuntimeException;
use DomainException;

final class ConfigFile implements ConfigFileInterface
{
    /**
     * @var array
     */
    private $i;
    
    /**
     * Cntr
     */
    public function __construct()
    {
        $this->i = [];
    }
    
    /**
     * @inheritDoc
     * @throws LogicException|RuntimeException|DomainException
     */
    public function loadFrom(string $pathfile): ConfigFileInterface
    {
        if (!empty($this->i)) {
            throw new LogicException("the instance already loaded");
        }
        if (!is_readable($pathfile)) {
            throw new RuntimeException("file=`$pathfile` is not exists or is not readable");
        }
        $i = (function () use ($pathfile) {
            return require_once $pathfile;
        }) ();
        if (!is_array($i)) {
            throw new DomainException("file=`$pathfile` is corrupted");
        }
        $that = $this->blueprinted();
        $that->i = $i;
        return $that;
    }
    
    /**
     * @inheritDoc
     */
    public function option(string $key, $default = null)
    {
        if (!isset($this->i[$key])) {
            $r = $default;
        } else {
            $r = $this->i[$key];
        }
        return $r;
    }
    
    /**
     * @return ConfigFile
     */
    public function blueprinted(): ConfigFile
    {
        $that = new self();
        $that->i = $this->i;
        return $that;
    }
}
