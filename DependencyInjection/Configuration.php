<?php
namespace EveryCheck\TestApiRestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('test_api_rest');

		$rootNode
			->children()
				->arrayNode('directory')
					->children()
						->scalarNode('payloads')
							->isRequired()
							->defaultValue('Payloads')
						->end()
						->scalarNode('responses')
							->isRequired()
							->defaultValue('Responses\Expected')
						->end()
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}				
}

?>