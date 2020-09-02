<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\Application\Kernel;
use Sylius\Bundle\UiBundle\Block\BlockEventListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Yaml\Yaml;

final class PayPlugSyliusPayPlugExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container)
    {
        if (\version_compare(Kernel::VERSION, '1.7.0', '<') === true) {
            // Current version is below 1.7, load legacy sonata block
            // This part can be dropped when we no longer support sylius < 1.7
            $this->loadSonataEvent($container);
        }
    }

    private function loadSonataEvent(ContainerBuilder $container)
    {
        $uiConfig = Yaml::parse(\file_get_contents(\dirname(__DIR__) . '/Resources/config/ui.yaml'));

        foreach ($uiConfig['sylius_ui']['events'] as $event => $blocks) {
            $tag = [
                'event' => 'sonata.block.event.' . $event,
                'method' => 'onBlockEvent'
            ];
            \array_map(function (array $data, string $key) use ($tag, $event, $container) {
                $key = 'payplug.' . $event . '.' . $key;
                $sonataEvent = $data['context']['legacy_event'] ?? $tag['event'];
                $definition = $container->register($key, BlockEventListener::class);
                $definition
                    ->addArgument($data['template'])
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->addTag(
                        'kernel.event_listener',
                        \array_merge($tag, ['event' => $sonataEvent])
                    );
            }, $blocks['blocks'], \array_keys($blocks['blocks']));
        }
    }
}
