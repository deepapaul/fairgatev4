<?php
/**
 * DoctrineExtensions Mysql Function Pack
 *
 * LICENSE
 *https://github.com/wiredmedia/doctrine-extensions/tree/master/lib/DoctrineExtensions/Query/Mysql
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Common\UtilityBundle\Extensions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class CheckActiveContact extends FunctionNode
{
    public $field1 = null;
    public $field2 = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field1 = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->field2 = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'checkActiveContact(' .
            $this->field1->dispatch($sqlWalker) . ', ' .
            $this->field2->dispatch($sqlWalker) .
        ')';
    }
}