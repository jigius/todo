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

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use RuntimeException;

final class Comments implements CommentsInterface
{
    /**
     * @var callable
     */
    private $f;
    
    public function __construct(callable $filter)
    {
        $this->f = $filter;
    }
    
    /**
     * @inheritDoc
     */
    public function extractedFromFile(string $pathfile): array
    {
        $parser =
            (new ParserFactory())
                ->create(ParserFactory::PREFER_PHP7);
        $fileContents = file_get_contents($pathfile);
        if ($fileContents === false) {
            throw new RuntimeException('File not readable: ' . $pathfile);
        }
        $ast = $parser->parse($fileContents);
        if ($ast === null) {
            throw new RuntimeException('File could not be parsed: ' . $pathfile);
        }
        $traverser = new NodeTraverser();
        $visitor = new class extends NodeVisitorAbstract
        {
            /**
             * @var callable
             */
            public $f;
            /**
             * @var array
             */
            public $c = [];
            
            public function enterNode(Node $node)
            {
                $comments = $node->getAttribute('comments');
                foreach ((array)$comments as $comment) {
                    /** @var Comment $comment */
                    if (call_user_func($this->f, $comment)) {
                        $this->c[] = $comment;
                    }
                }
            }
        };
        $visitor->f = $this->f;
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);
        $r = [];
        /**
         * FIXME: I do not known why but comments have duplicates.
         * Temporary solution is excludes them with hash keys ...
         */
        foreach ($visitor->c as $c) {
            /** @var Comment $c */
            $t = [
                'startline' => $c->getStartLine(),
                'file' => $pathfile,
                'text' => $c->getReformattedText()
            ];
            $r[hash("sha1", implode("@", $t))] = $t;
        }
        return array_values($r);
    }
}
