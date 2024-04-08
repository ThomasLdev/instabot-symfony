<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task')]
class TaskController extends BaseController
{
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
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

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (null === $user) {
                throw $this->createAccessDeniedException('User not found.');
            }

            /** @var User $user */
            $task->setUser($user);

            $entityManager->persist($task);
            $entityManager->flush();

            return $this->flashOnRedirect(
                'success',
                'Your task has been created successfully.',
                'app_task_index');
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        if (false === $this->isAuthorized($task)) {
            throw $this->createAccessDeniedException('You are not authorized to view this task.');
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if (false === $this->isAuthorized($task)) {
            throw $this->createAccessDeniedException('You are not authorized to edit this task.');
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->flashOnRedirect(
                'success',
                'Your task has been updated successfully.',
                'app_task_index'
            );
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if (false === $this->isAuthorized($task)) {
            throw $this->createAccessDeniedException('You are not authorized to delete this task.');
        }

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->flashOnRedirect(
            'success',
            'Your task has been deleted successfully.',
            'app_task_index'
        );
    }

    private function isAuthorized(Task $task): bool
    {
        $user = $this->getUser();

        if (null === $user) {
            return false;
        }

        return $task->getUser() === $user;
    }
}
