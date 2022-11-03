<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ApplePayChecker implements ApplePayCheckerInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function isDeviceReady(): bool
    {
        return true;
        $mainRequest = $this->requestStack->getMainRequest();
        if (!$mainRequest instanceof Request) {
            return false;
        }

        $userAgent = $mainRequest->headers->get('User-Agent');

        if (!is_string($userAgent)) {
            return false;
        }

        $browsers = ["Opera", "Edg", "Chrome", "Firefox", "MSIE", "Trident"];

        foreach ($browsers as $browser) {
            if (strpos($userAgent, $browser) !== false) {
                return false;
            }
        }

        preg_match("/iPhone|Android|iPad|iPod|webOS|Mac/", $userAgent, $matches);
        $os = current($matches);

        return \is_string($os);
    }
}
