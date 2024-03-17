<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Api\GoogleDrive\DriveRequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractController
{
    public function index(DriveRequestService $driveRequestService): Response
    {
        return $this->render('task/index.html.twig', [
            'files' => $driveRequestService->getDriveFiles($this->getUser()),
        ]);
    }
}
