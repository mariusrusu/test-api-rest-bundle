<?php
namespace EveryCheck\TestApiRestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder("test_api_rest");
		$rootNode = $treeBuilder->getRootNode();

		$rootNode
			->children()
				->arrayNode('directory')
		            ->addDefaultsIfNotSet()
					->children()
						->scalarNode('payloads')
							->isRequired()
							->defaultValue('Payloads')
						->end()
						->scalarNode('responses')
							->isRequired()
							->defaultValue('Responses/Expected')
						->end()
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}				
}

?>