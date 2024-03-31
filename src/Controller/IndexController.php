<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\Google\GoogleDriveService;
use App\Service\Security\TokenService;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SodiumException;
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
     * @throws SodiumException
     */
    #[Route('/', name: 'app_index')]
    public function index(GoogleDriveService $driveService, TokenService $tokenService): Response
    {
        return $this->render('index/index.html.twig', [
            'files' => $driveService->getFilesForUser($this->getUserToken($tokenService))
        ]);
    }

    /**
     * @throws SodiumException
     */
    private function getUserToken(TokenService $tokenService): ?string
    {
        $user = $this->getUser();

        $token = $user?->getSettings()?->getGoogleDriveToken();

        if (null === $token || '' === $token) {
            return null;
        }

        return $tokenService->decrypt($token);
    }
}
