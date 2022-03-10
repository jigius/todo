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

use RuntimeException;

/**
 * Replaces all absolute paths to files with relative ones
 */
final class CommentsWithRelativeFilePath implements CommentsInterface
{
    /**
     * @var CommentsInterface
     */
    private $origin;
    /**
     * @var string
     */
    private $basedir;
    
    /**
     * Cntr
     * @param CommentsInterface $c
     * @param string $basedir
     */
    public function __construct(CommentsInterface $c, string $basedir)
    {
        $this->origin = $c;
        $this->basedir = $basedir;
    }
    
    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function extractedFromFile(string $pathfile): array
    {
        $baseDir = realpath($this->basedir);
        if ($baseDir === false) {
            throw new RuntimeException("basedir=`$pathfile` is not exists");
        }
        return
            array_map(
                function (array $c) use ($baseDir) {
                    if (
                        isset($c['file']) &&
                        is_string($c['file']) &&
                        ($rp = realpath($c['file'])) !== false &&
                        mb_substr($rp, 0, mb_strlen($baseDir)) === $baseDir
                    ) {
                        $c['file'] = mb_substr($rp, mb_strlen($baseDir) + 1);
                    }
                    return $c;
                },
                $this->origin->extractedFromFile($pathfile)
            );
    }
}
