<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\Gateway\Validator\Constraints;

use Payplug\Exception\UnauthorizedException;
use PayPlug\SyliusPayPlugPlugin\ApiClient\PayPlugApiClientFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @Annotation
 */
final class IsPayPlugSecretKeyValidator extends ConstraintValidator
{
    /** @var PayPlugApiClientFactory */
    private $apiClientFactory;

    public function __construct(PayPlugApiClientFactory $apiClientFactory)
    {
        $this->apiClientFactory = $apiClientFactory;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsPayPlugSecretKeyValid) {
            throw new UnexpectedTypeException($constraint, IsPayPlugSecretKeyValid::class);
        }
        if (null === $value || '' === $value) {
            return;
        }
        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            \Payplug\Payplug::init(['secretKey' => $value]);
            \Payplug\Authentication::getPermissions();
            $apiClient = $this->apiClientFactory->create('payplug_oney', $value);
            $apiClient->getAccount(true);
        } catch (UnauthorizedException $exception) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
