<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Google\GoogleDriveClientService;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DriveFilesController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/drive/files', name: 'app_drive_files')]
    public function index(GoogleDriveClientService $driveService): Response
    {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->addFlash('error', 'You must be logged in to access your Google Drive files.');

            return $this->redirectToRoute('app_login');
        }

        try {
            $files = $driveService->getFilesForUser($user->getSettings());
        } catch (Exception $e) {
            $this->addFlash('error', 'You did not provide any folder ID or did not authorize the app.');

            return $this->redirectToRoute('app_settings');

        }

        return $this->render('drive_files/index.html.twig', [
            'files' => $files,
        ]);
    }
}
