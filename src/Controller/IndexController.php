<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\Google\GoogleDriveService;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws \Google\Exception
     * @throws ContainerExceptionInterface
     */
    #[Route('/', name: 'app_index')]
    public function index(GoogleDriveService $driveService): Response
    {
        return $this->render('index/index.html.twig', ['files' => $driveService->getFilesForUser()]);
    }
}
