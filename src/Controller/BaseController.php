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

    public function translateFlash(string $translateKey): string
    {
        return $this->translator->trans($translateKey);
    }

    public function getUserSettings(): ?UserSettings
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user->getSettings();
    }
}
