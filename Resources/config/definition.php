<?php

use Kotaru\SuluUtils\Entity\Setting;
use Kotaru\SuluUtils\Repository\SettingsRepository;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
            $definition->rootNode()
            ->children()
                ->arrayNode('objects')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('settings')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(Setting::class)->end()
                                ->scalarNode('repository')->defaultValue(SettingsRepository::class)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end() // objects
                ->arrayNode('location')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_zoom')->defaultValue(8)->end()
                        ->arrayNode('default_center')->defaultValue([0, 0])
                            ->acceptAndWrap(['string','int'])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end() // location
                ->arrayNode('styles')
                    ->arrayPrototype()
                        ->children()
                            ->stringNode('label')->cannotBeEmpty()->end()
                            ->stringNode('element')->cannotBeEmpty()->end()
                            ->arrayNode('classes')
                                ->acceptAndWrap(['string'])
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end() // location

            ->end();
};
