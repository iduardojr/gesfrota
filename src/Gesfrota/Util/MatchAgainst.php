<?php
namespace Gesfrota\Util;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * MATCH({StateFieldPathExpression ","}*) AGAINST(:searchterm InParameter[boolean|expand])>0'
 * MATCH(u.field1, u.field2, u.field3) AGAINST(:searchterm boolean)>0'
 */
class MatchAgainst extends FunctionNode {
	
	/** 
	 * @var array list of \Doctrine\ORM\Query\AST\PathExpression
	 */
	protected $pathExp = null;
	
	/** 
	 * @var string
	 */
	protected $against = null;
	
	/** 
	 * @var bool 
	 */
	protected $booleanMode = false;
		
	/** 
	 * @var bool 
	 */
	protected $queryExpansion = false;
	
	public function parse(\Doctrine\ORM\Query\Parser $parser) {
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		
		$this->pathExp = [];
		$this->pathExp[] = $parser->StateFieldPathExpression();
		
		$lexer = $parser->getLexer();
		while ($lexer->isNextToken(Lexer::T_COMMA)) {
			$parser->match(Lexer::T_COMMA);
			$this->pathExp[] = $parser->StateFieldPathExpression();
		}
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
		
		if (strtolower($lexer->lookahead['value']) !== 'against') {
			$parser->syntaxError('against');
		}
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->against = $parser->StringPrimary();
		if (strtolower($lexer->lookahead['value']) === 'boolean') {
			$parser->match(Lexer::T_IDENTIFIER);
			$this->booleanMode = true;
		}
		if (strtolower($lexer->lookahead['value']) === 'expand') {
			$parser->match(Lexer::T_IDENTIFIER);
			$this->queryExpansion = true;
		}
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}
	
	public function getSql(\Doctrine\ORM\Query\SqlWalker $walker) {
		$fields = [];
		foreach ($this->pathExp as $pathExp) {
			$fields[] = $pathExp->dispatch($walker);
		}
		$against = $walker->walkStringPrimary($this->against);
		$against.= ($this->booleanMode ? ' IN BOOLEAN MODE' : '');
		$against.= ($this->queryExpansion ? ' WITH QUERY EXPANSION' : '');
		return sprintf('MATCH (%s) AGAINST (%s)', implode(', ', $fields), $against);
	}
}
?>