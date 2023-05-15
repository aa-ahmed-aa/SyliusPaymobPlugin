<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ahmedkhd_sylius_paymob_plugin');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
