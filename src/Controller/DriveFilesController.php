<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Google\Drive\GoogleDriveClientService;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DriveFilesController extends AbstractController
{
    private const LOGIN_ROUTE = 'app_login';
    private const SETTINGS_ROUTE = 'app_settings';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/drive/files', name: 'app_drive_files')]
    public function index(
        GoogleDriveClientService $driveService,
    ): Response|RedirectResponse {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            return $this->redirectOnError('errors.controller.user.not_logged', self::LOGIN_ROUTE);
        }

        $settings = $user->getSettings();

        if (null === $settings) {
            return $this->redirectOnError('errors.controller.user.no_settings', self::SETTINGS_ROUTE);
        }

        try {
            $response = $driveService->getFilesForUser($settings);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return $this->redirectOnError('errors.drive.general', self::SETTINGS_ROUTE);
        }

        if (false === $response->getSuccess()) {
            return $this->redirectOnError($response->getMessage(), self::SETTINGS_ROUTE);
        }

        return $this->render('drive_files/index.html.twig', [
            'files' => $response->getFiles(),
        ]);
    }

    private function redirectOnError(string $message, string $route): RedirectResponse
    {
        $this->addFlash('error', $this->translator->trans($message));

        return $this->redirectToRoute($route);
    }
}
