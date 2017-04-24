<?php

/**
* DoctrineExtensions Mysql Function Pack
*
* LICENSE
*
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to kontakt@beberlei.de so I can send you a copy immediately.
*/

namespace Common\UtilityBundle\Extensions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
    Doctrine\ORM\Query\Lexer;

/**
* Usage: FIELD(str,str1,str2,str3,...)
*
* FIELD returns the index (position) of str in the str1, str2, str3, ... list.
* Returns 0 if str is not found. If all arguments to FIELD() are strings, all
* arguments are compared as strings. If all arguments are numbers, they are
* compared as numbers. Otherwise, the arguments are compared as double.
* If str is NULL, the return value is 0 because NULL fails equality comparison
* with any value. FIELD() is the complement of ELT(). (Taken from MySQL
* documentation.)
*
* @author  Jeremy Hicks <jeremy.hicks@gmail.com>
* @version Release:1
*/

class FetchContactNameNoSort extends FunctionNode
{
    private $field = null;
    private $field1 = null;
    private $values = array();

    /**
     * This function is used to parse value
     * @param Object $parser parser
     *
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->ArithmeticPrimary();
        $this->field1 = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * This function is used to get Sql
     * @param Object $sqlWalker sqlWalker
     *
     * @return sql
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $query = 'contactNameNoSort(';
        $query .= $this->field->dispatch($sqlWalker);
        $query .= ',';
        $query .= $this->field1->dispatch($sqlWalker);
        $query .= ')';

        return $query;
    }
}
