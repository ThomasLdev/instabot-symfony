<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserSettings;
use App\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_settings')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserSettingsType::class, $this->getUserSettings());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveUserSettings($form->getData(), $entityManager);
            $this->addFlash('success', 'Your settings have been saved.');
        }

        return $this->render('settings/form.html.twig', [
            'settingsForm' => $form->createView(),
        ]);
    }

    private function getUserSettings(): UserSettings
    {
        /** @var User $user */
        $user = $this->getUser();

        $settings = $user->getSettings();

        if (null === $settings) {
            $settings = new UserSettings();
            $user->setSettings($settings);
        }

        return $settings;
    }

    private function saveUserSettings(UserSettings $settings, EntityManagerInterface $entityManager): void
    {
        $entityManager->persist($settings);
        $entityManager->flush();
    }
}
