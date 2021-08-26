<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Webmozart\Assert\Assert;

final class SavedCardsController extends AbstractController
{
    public function __invoke(
        Request $request,
        CustomerContextInterface $customerContext,
        EntityManagerInterface $entityManager
    ): Response {
        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('submit', SubmitType::class);
        
        return $this->render('@PayPlugSyliusPayPlugPlugin/customer_account/saved_cards.html.twig', [
            'form' => $formBuilder->getForm(),
        ]);
    }
}