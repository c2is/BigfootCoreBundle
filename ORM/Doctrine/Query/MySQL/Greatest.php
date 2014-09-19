<?php

namespace Bigfoot\Bundle\CoreBundle\ORM\Doctrine\Query\MySQL;

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
*
*
* Example Usage:
* $query = $this->getEntityManager()->createQuery('SELECT A FROM Entity A WHERE REGEXP(A.stringField, :regexp) = 1');
* $query->setParameter('regexp', '^[ABC]');
* $results = $query->getArrayResult();
*/

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class Greatest extends FunctionNode
{
    public $values = array();

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->values[] = $parser->ScalarExpression();

        $lexer = $parser->getLexer();
        while ($lexer->lookahead['type'] == Lexer::T_COMMA) {
            $parser->match(Lexer::T_COMMA);
            $peek = $lexer->glimpse();

            $this->values[] = $peek['value'] == '('
                ? $parser->FunctionDeclaration()
                : $parser->ScalarExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $values = array();
        foreach ($this->values as $value) {
            $values[] = $value->dispatch($sqlWalker);
        }

        return 'GREATEST(' . implode(',', $values) . ')';
    }
}
