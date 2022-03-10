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

use Iterator;
use RecursiveIteratorIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use SplFileInfo;

final class PhpFiles implements PhpFilesInterface
{
    /**
     * @var ConfigOptionInterface
     */
    private $e;
    /**
     * @var ConfigOptionInterface
     */
    private $i;
    /**
     * @var string
     */
    private $baseDir;
    
    /**
     * Cntr
     */
    public function __construct(ConfigOptionInterface $includes, ConfigOptionInterface $excludes, string $baseDir)
    {
        $this->i = $includes;
        $this->e = $excludes;
        $this->baseDir = $baseDir;
    }
    
    /**
     * @inheritDoc
     */
    public function iterator(): Iterator
    {
        return
            new RecursiveIteratorIterator(
                new RecursiveCallbackFilterIterator(
                    new RecursiveDirectoryIterator(
                        $this->baseDir,
                        FilesystemIterator::CURRENT_AS_FILEINFO|
                        FilesystemIterator::SKIP_DOTS|
                        FilesystemIterator::FOLLOW_SYMLINKS
                    ),
                    function (SplFileInfo $current, $key, $iterator) {
                        if (!empty($this->e->value())) {
                            if ($this->test($current->getRealPath(), $this->e->value())) {
                                return false;
                            }
                        }
                        if ($iterator->hasChildren()) {
                            return true;
                        }
                        if (!empty($this->i->value())) {
                            if (!$this->test($current->getRealPath(), $this->i->value())) {
                                return false;
                            }
                        }
                        return $current->getExtension() === "php";
                    }
                )
            );
    }
    
    /**
     * Tests if a passed path file is equals to or is prefixed with one of item into passed list
     * @param string $pathfile
     * @param array $list
     * @return bool
     */
    private function test(string $pathfile, array $list): bool
    {
        $ret = false;
        $t0 = realpath($pathfile);
        if ($t0) {
            foreach ($list as $l) {
                $t1 = realpath($this->baseDir . "/" . $l);
                if ($t1 !== false && mb_substr($t0, 0, mb_strlen($t1)) === $t1) {
                    $ret = true;
                    break;
                }
            }
        }
        return $ret;
    }
}
