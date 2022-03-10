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

/**
 * Prints a comment into stdout
 * @throws LogicException
 */
final class PrnComment implements PrinterInterface
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
     */
    public function with($key, $val)
    {
        $that = $this->blueprinted();
        $that->i[$key] = $val;
        return $that;
    }
    
    /**
     * @inheritDoc
     * @return void
     * @throws LogicException
     */
    public function finished()
    {
        if (
            !isset($this->i['file']) ||
            !is_string($this->i['file']) ||
            !isset($this->i['text']) ||
            !is_string($this->i['text']) ||
            !isset($this->i['startline']) ||
            !is_numeric($this->i['startline']) ||
            !isset($this->i['hash']) ||
            !is_string($this->i['hash'])
        ) {
            var_dump($this->i);
            throw new LogicException("data is corrupted");
        }
        echo <<< EOT
--
{$this->i['hash']}
{$this->i['file']}:{$this->i['startline']}
{$this->i['text']}

EOT;
    }
    
    /**
     * @return PrnComment
     */
    public function blueprinted(): self
    {
        $that = new self();
        $that->i = $this->i;
        return $that;
    }
}
