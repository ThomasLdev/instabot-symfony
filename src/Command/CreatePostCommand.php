<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Command;

use App\Entity\Task;
use App\Entity\UserSettings;
use App\Service\Google\GoogleDriveClientService;
use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsCommand(
    name: 'app:create-post',
    description: 'Create an instagram post based on google drive media, at a given time.',
)]
class CreatePostCommand extends Command
{
    public function __construct(
        //        private readonly EntityManagerInterface $entityManager,
        //        private readonly GoogleDriveClientService $googleDriveService
    ) {
        parent::__construct();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //        $output->writeln('Starting to create post...');
        //
        //        // TODO 1 : Get all tasks by user
        //        $tasks = $this->getTasks();
        //
        //        if (empty($tasks)) {
        //            $output->writeln('No tasks found.');
        //
        //            return Command::SUCCESS;
        //        }
        //
        //        // TODO 2 : for each user task, check if it's due
        //        foreach ($tasks as $task) {
        //            if (false === $task->getUser() instanceof UserInterface) {
        //                $this->entityManager->remove($task);
        //            }
        //
        //            // TODO 3 : if task is due, run tasks
        //            if ($this->isTaskDue($task)) {
        //                $this->runTask($task);
        //            }
        //        }
        //
        //        // TODO 4 : create post with files
        //        // TODO 5 : create TaskLog with responses

        return Command::SUCCESS;
    }

    //    /**
    //     * @throws ContainerExceptionInterface
    //     * @throws NotFoundExceptionInterface
    //     * @throws Exception
    //     */
    //    private function runTask(Task $task): void
    //    {
    //        // TODO 4 : get files from google drive user folder ID
    //        $settings = $task->getUser()?->getSettings();
    //
    //        if (false === $settings instanceof UserSettings) {
    //            return;
    //        }
    //
    //        try {
    //            $files = $this->googleDriveService->getFilesForUser($settings);
    //        } catch (Exception $e) {
    //            // TODO 5 : create TaskLog with error
    //        }
    //

    //    }
    //
    //    private function getTasks(): array
    //    {
    ////        return $this->entityManager->getRepository(Task::class)->findAll();
    //
    //        return [];
    //    }
    //
    //    private function isTaskDue(Task $task): bool
    //    {
    ////        return (new CronExpression($task->getCronExpression()))->isDue();
    //
    //        return false;
    //    }
}
