<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseController extends AbstractController
{
    protected const LOGIN_ROUTE = 'app_login';
    protected const INDEX_ROUTE = 'app_index';
    protected const SETTINGS_ROUTE = 'app_settings';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function flashOnRedirect(string $type, string $message, string $route): RedirectResponse
    {
        $this->addFlash($type, $this->translator->trans($message));

        return $this->redirectToRoute($route);
    }

    public function getAppUser(): User
    {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->flashOnRedirect('error', 'errors.controller.user.not_logged',self::LOGIN_ROUTE);
        }

        return $user;
    }

    public function getAppUserSettings(User $user): UserSettings
    {
        $settings = $user->getSettings();

        if (null === $settings) {
            $this->flashOnRedirect(
                'error',
                'errors.controller.user.no_settings',
                self::SETTINGS_ROUTE
            );
        }

        return $settings;
    }

    public function translateFlash(string $translateKey): string
    {
        return $this->translator->trans($translateKey);
    }
}
