<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils;

use Kotaru\SuluUtils\Repository\SettingsRepository;
use Kotaru\SuluUtils\Entity\Setting;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SuluUtilsBundle extends AbstractBundle
{
    use PersistenceBundleTrait, PersistenceExtensionTrait;

    public const SYSTEM_COLLECTION_ROOT = 'sulu_utils';

    public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig(
                'doctrine',
                [
                    'orm' => [
                        'mappings' => [
                            'SuluUtils' => [
                                'type' => 'xml',
                                'dir' => __DIR__ . '/Resources/config/doctrine',
                                'prefix' => 'Kotaru\SuluUtils\Entity',
                                'alias' => 'SuluUtils',
                            ],
                        ],
                    ],
                ]
            );
        }
        if ($container->hasExtension('jms_serializer')) {
            $container->prependExtensionConfig(
                'jms_serializer',
                [
                    'metadata' => [
                        'directories' => [
                            [
                                'name' => 'sulu_utils',
                                'path' => __DIR__ . '/Resources/config/serializer',
                                'namespace_prefix' => 'Kotaru\SuluUtils\Entity',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('framework')) {
            $container->prependExtensionConfig(
                'framework',
                [
                    'translator' => [
                        'paths' => [
                            __DIR__ . '/Resources/translations',
                        ],
                    ],
                ]
            );
            }

    }


    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $parameters = $container->parameters();

        $parameters->set('sulu_utils.default_zoom', $config['location']['default_zoom']);
        $parameters->set('sulu_utils.default_center', $config['location']['default_center']);
        $parameters->set('sulu_utils.editor_styles', $config['styles']);

        $container->import(__DIR__ . '/Resources/config/services.php');



        if ($config['objects']) {
            $this->configurePersistence($config['objects'], $builder);
        }
    }

    public function getPath(): string
    {
        if (!isset($this->path)) {
            $reflected = new \ReflectionObject($this);
            // assume the modern directory structure by default
            $this->path = \dirname($reflected->getFileName());
        }

        return $this->path;
    }
}
