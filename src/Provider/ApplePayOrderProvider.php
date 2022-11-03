<?php

namespace PayPlug\SyliusPayPlugPlugin\Provider;

use LogicException;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ApplePayOrderProvider
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    public function getCurrentCart(): ?int
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request){
            throw new LogicException();
        }

        $orderId = $request->attributes->get('orderId');

        if (null === $orderId || '' === $orderId) {
            throw new LogicException();
        }

        return $orderId;
    }
}
