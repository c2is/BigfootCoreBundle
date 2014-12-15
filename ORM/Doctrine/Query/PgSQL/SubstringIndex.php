<?php

namespace Bigfoot\Bundle\CoreBundle\ORM\Doctrine\Query\PgSQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;

/**
 * Class SubstringIndex
 * @package Bigfoot\Bundle\CoreBundle\ORM\Doctrine\Query\PgSQL
 */
class SubstringIndex extends FunctionNode
{
    /** @var Node */
    public $value  = null;
    /** @var Node */
    public $delimiter = null;
    /** @var Node */
    public $count = null;

    /**
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->value = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);

        $this->delimiter = $parser->StringExpression();
        $parser->match(Lexer::T_COMMA);

        $this->count = $parser->SimpleArithmeticExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'SPLIT_PART(' . $this->value->dispatch($sqlWalker) . ', ' . $this->delimiter->dispatch($sqlWalker) . ', ' . $this->count->dispatch($sqlWalker) . ')';
    }
}
