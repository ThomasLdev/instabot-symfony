<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\Google\Drive\GoogleDriveClientService;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DriveFilesController extends BaseController
{
    /**
     * @throws Exception
     */
    #[Route('/drive/files', name: 'app_drive_files')]
    public function index(GoogleDriveClientService $driveService): Response|RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $settings = $this->getUserSettings();

        if (null === $settings) {
            return $this->flashOnRedirect(
                'error',
                'errors.controller.drive.no_settings',
                self::SETTINGS_ROUTE
            );
        }

        $response = $driveService->getFilesForUser($settings);

        if (false === $response->getSuccess()) {
            return $this->flashOnRedirect('error', $response->getMessage(), self::SETTINGS_ROUTE);
        }

        return $this->render('drive_files/index.html.twig', [
            'files' => $response->getFiles(),
        ]);
    }
}
