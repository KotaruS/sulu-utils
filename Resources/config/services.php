<?php

// namespace Kotaru\Bundle\SuluNewsBundle;
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Kotaru\SuluUtils\Admin\UtilsAdmin;
use Kotaru\SuluUtils\Content\Type\Location;
use Kotaru\SuluUtils\Content\Type\MapPoints;
use Kotaru\SuluUtils\Content\Type\Range;
use Kotaru\SuluUtils\Controller\Website\RedirectController;
use Kotaru\SuluUtils\Doctrine\DoctrineListRepresentationFactory;
use Kotaru\SuluUtils\Common\MediaCopier;
use Kotaru\SuluUtils\Common\SerializerInterface;
use Kotaru\SuluUtils\Common\VideoParser;
use Kotaru\SuluUtils\Controller\Admin\AuthController;
use Kotaru\SuluUtils\Entity\Setting;
use Kotaru\SuluUtils\Link\LocalLinkProvider;
use Kotaru\SuluUtils\Link\PopupLinkProvider;
use Kotaru\SuluUtils\Manager\SuluMediaManager;
use Kotaru\SuluUtils\Repository\SettingsRepository;
use Kotaru\SuluUtils\Controller\Website\TranslationController;
use Kotaru\SuluUtils\Twig\CodeParserTwigExtension;
use Kotaru\SuluUtils\Twig\PageResolverTwigExtension;
use Kotaru\SuluUtils\Twig\TestExtrasExtension;
use Kotaru\SuluUtils\Twig\UtilsExtension;
use Kotaru\SuluUtils\Twig\VideoTwigExtension;
use Sulu\Bundle\WebsiteBundle\Resolver\RequestAnalyzerResolverInterface;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sulu_utils.entity.settings', SettingsRepository::class);

    $services->alias(
        RequestAnalyzerResolverInterface::class,
        'sulu_website.resolver.request_analyzer'
    )->public();

    $services->alias(
        ArraySerializerInterface::class,
        'sulu_core.array_serializer'
    )->public();

    $services->alias(
        SerializerInterface::class,
        'jms_serializer.serializer'
    )->public();

    $services->set(SettingsRepository::class)
        ->public()
        ->tag('doctrine.repository_service')
        ->args([new Reference('doctrine'), '%sulu_utils.entity.settings%'])
        ->alias('sulu_utils.repository.settings', SettingsRepository::class, )
    ;

    // Admin
    $services->set(UtilsAdmin::class)
        ->args([
            '%sulu_utils.default_center%',
            '%sulu_utils.default_zoom%',
            '%sulu_utils.editor_styles%',
            ])
        ->tag('sulu.admin')
        ->tag('sulu.context', ['context' => 'admin'])
    ;

    // Doctrine / Controller utility
    $services->set(DoctrineListRepresentationFactory::class)
        ->args([
            new Reference('sulu_core.rest_helper'),
            new Reference('sulu_core.list_rest_helper'),
            new Reference('sulu_core.doctrine_list_builder_factory'),
            new Reference('sulu_core.list_builder.field_descriptor_factory'),
            new Reference('doctrine.orm.entity_manager'),
        ]);

    // Common
    $services->set(MediaCopier::class)
        ->args([
            new Reference('sulu_media.media_manager'),
            new Reference('sulu.repository.media'),
            new Reference('sulu_media.storage'),
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_activity.domain_event_collector'),
            new Reference('sulu.content.path_cleaner'),
            new Reference('sulu_media.type_manager'),
        ]);

    $services->set(VideoParser::class)
        ->args([
            new Reference('request_stack'),
        ])
    ;

    // Manager
    $services->set(SuluMediaManager::class)
        ->args([
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_media.storage'),
            new Reference('sulu_media.format_manager'),
            new Reference('sulu_security.security_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            new Reference('sulu_trash.trash_manager', ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ])
    ;

    // Content Types
    $services->set(Location::class)
    ->public()
    ->tag('sulu.content.type', ['alias'=> 'location']);

    $services->set(MapPoints::class)
    ->public()
     ->args([
         '%sulu_utils.default_center%',
         '%sulu_utils.default_zoom%',
        ])
    ->tag('sulu.content.type', ['alias'=> 'map_points']);

    $services->set(Range::class)
    ->public()
    ->tag('sulu.content.type', ['alias'=> 'range']);

    // Link providers
    $services->set(LocalLinkProvider::class)
    ->public()
    ->tag('sulu.link.provider', ['alias'=> 'local']);

    // Controllers
    $services->set(AuthController::class)
        ->public()
        ->args([
            new Reference('sulu_document_manager.document_manager'),
            new Reference('sulu_security.security_checker'),
            new Reference(UrlGeneratorInterface::class),
            new Reference('sulu_admin.view_registry', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
        ])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
        ->call('setContainer', [service(\Psr\Container\ContainerInterface::class)])
        // ->tag('sulu.context', ['context' => 'admin'])
        ->alias('sulu_utils.auth_controller', AuthController::class)
        ->public()
    ;
    $services->set(TranslationController::class)
        ->public()
        ->args([
            new Reference('translator'),
        ])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
        ->call('setContainer', [service(\Psr\Container\ContainerInterface::class)]);

    $services->alias('sulu_utils.translations_controller', TranslationController::class)
        ->public();

    $services->set(RedirectController::class)
        ->public()
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
        ->call('setContainer', [service(\Psr\Container\ContainerInterface::class)]);

    $services->alias('sulu_utils.translations_controller', TranslationController::class)
        ->public();

    // Twig
    $services->set(PageResolverTwigExtension::class)
        ->args([
            new Reference('sulu_document_manager.document_manager'),
            '%default_locale%',
        ])
        ->tag('twig.extension');

    $services->set(CodeParserTwigExtension::class)
        ->args([
            new Reference(VideoParser::class),
        ])
        ->tag('twig.extension');

    $services->set(VideoTwigExtension::class)
        ->args([
            new Reference(VideoParser::class),
        ])
        ->tag('twig.extension');

    $services->set(UtilsExtension::class)
        ->args([
            '%sulu_media.media.storage.local.path%',
            new Reference(DecoderInterface::class),
            new Reference('sulu_utils.repository.settings'),
        ])
        ->tag('twig.extension');

    $services->set(TestExtrasExtension::class)
        ->tag('twig.extension');

};
