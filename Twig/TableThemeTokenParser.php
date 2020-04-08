<?php

namespace Voelkel\DataTablesBundle\Twig;

use Twig\Node\Expression\ArrayExpression;

class TableThemeTokenParser extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $table = $this->parser->getExpressionParser()->parseExpression();

        $resources = new ArrayExpression([], $stream->getCurrent()->getLine());
        do {
            $resources->addElement($this->parser->getExpressionParser()->parseExpression());
        } while (!$stream->test(\Twig\Token::BLOCK_END_TYPE));

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new TableThemeNode($table, $resources, $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'table_theme';
    }
}
