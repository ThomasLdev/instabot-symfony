<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route(path: '/task', name: 'app_task_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (null === $user) {
            throw $this->createAccessDeniedException('User not found.');
        }

        /** @var User $user */
        return $this->render('task/index.html.twig', [
            'tasks' => $user->getTasks(),
        ]);
    }
}
