<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailConfirmation(
        string $verifyEmailRouteName,
        UserInterface $user,
        TemplatedEmail $template
    ): void {
        /** @var User $user */
        $email = $user->getEmail();
        $userId = $user->getId();

        if (null === $email || null === $userId) {
            return;
        }

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string)$user->getId(),
            $email
        );

        $context = $template->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $template->context($context);

        $this->mailer->send($template);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        /** @var User $user */
        $email = $user->getEmail();
        $userId = $user->getId();

        if (null === $email || null === $userId) {
            return;
        }

        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), (string)$userId, $email);

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
