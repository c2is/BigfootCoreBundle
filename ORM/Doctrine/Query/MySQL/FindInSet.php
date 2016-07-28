<?php

namespace Bigfoot\Bundle\CoreBundle\ORM\Doctrine\Query\MySQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class FindInSet
 * @package Bigfoot\Bundle\CoreBundle\ORM\Doctrine\Query\MySQL
 */
class FindInSet extends FunctionNode
{
    /** @var Node */
    public $needle = null;

    /** @var Node */
    public $haystack = null;

    /**
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->needle = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->haystack = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'FIND_IN_SET(' . $this->needle->dispatch($sqlWalker) . ', ' . $this->haystack->dispatch($sqlWalker) . ')';
    }
}
