<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\Controller;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class OneySimulationPopin extends AbstractOneyController
{
    public function __invoke(Request $request): Response
    {
        /** @var \Sylius\Component\Core\Model\OrderInterface $cart */
        $cart = $this->cartContext->getCart();

        /** @var string|null $productCode */
        $productCode = $request->get('product');

        /** @var string|null $productOptionValue */
        $productOptionValue = $request->get('option');

        /** @var int|null $quantity */
        $quantity = (int) $request->get('quantity');

        if (null === $productCode || null === $quantity) {
            $simulationData = $this->oneySimulationDataProvider->getForCart($cart);

            return $this->render(
                '@PayPlugSyliusPayPlugPlugin/oney/popin.html.twig',
                [
                    'data' => $simulationData,
                    'ineligibilityData' => [],
                ]
            );
        }

        return $this->renderSimulateForProductVariant($cart, $productCode, $quantity, $productOptionValue);
    }

    private function renderSimulateForProductVariant(
        OrderInterface $cart,
        string $productCode,
        int $quantity,
        ?string $productOptionValue
    ): Response {
        $productVariant = $this->getProductVariant($productCode, $productOptionValue);
        Assert::isInstanceOf($productVariant, ProductVariantInterface::class);

        $channel = $cart->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        Assert::notNull($cart->getLocaleCode());
        Assert::notNull($cart->getCurrencyCode());

        $tempCart = $this->createTempCart(
            $productVariant,
            $quantity,
            $channel,
            $cart->getLocaleCode(),
            $cart->getCurrencyCode()
        );

        $simulationData = $this->oneySimulationDataProvider->getForCart($tempCart);

        return $this->render(
            '@PayPlugSyliusPayPlugPlugin/oney/popin.html.twig',
            [
                'data' => $simulationData,
                'ineligibilityData' => $this->oneyRulesExtension->getReasonsOfIneligibility($tempCart),
            ]
        );
    }
}
