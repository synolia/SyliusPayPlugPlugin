<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\EventSubscriber;

use Sylius\Bundle\ShopBundle\Menu\AccountMenuBuilder;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AccountMenuEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AccountMenuBuilder::EVENT_NAME => 'handle',
        ];
    }
    
    public function handle(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu
            ->addChild('saved_cards', [
                'route' => 'payplug_sylius_shop_account_saved_cards'
            ])
            ->setLabel('payplug_sylius_payplug_plugin.menu.shop.account.saved_cards')
            ->setLabelAttribute('icon', 'credit card outline')
        ;
    }
}
