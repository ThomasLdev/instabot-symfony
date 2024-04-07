<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserSettings;
use App\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends BaseController
{
    #[Route('/settings', name: 'app_settings')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserSettingsType::class, $this->getAppUserSettings($this->getAppUser()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userSettings = $form->getData();

            if (false === $userSettings instanceof UserSettings) {
                return $this->flashOnRedirect(
                    'error',
                    'errors.controller.user.no_settings',
                    self::SETTINGS_ROUTE
                );
            }

            $entityManager->persist($userSettings);
            $entityManager->flush();

            $this->addFlash('success', $this->translateFlash('form.settings.saved'));
        }

        return $this->render('settings/form.html.twig', [
            'settingsForm' => $form->createView(),
        ]);
    }
}
