<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Command;

use App\Service\Google\GoogleDriveService;
use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-post',
    description: 'Create an instagram post based on google drive media, at a given time.',
)]
class CreatePostCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GoogleDriveService $googleDriveService
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
        $output->writeln('Starting to create post...');

        // TODO 1 : Get all tasks by user
        // TODO 2 : for each user task, check if it's due
        // TODO 3 : if task is due, get files from google drive
        // TODO 4 : create post with files
        // TODO 5 : create TaskLog with responses

        $files = $this->googleDriveService->getFiles();

        return Command::SUCCESS;
    }

    private function getTasks(): array
    {
        return $this->entityManager->getRepository(Task::class)->findAll();
    }

    private function isTaskDue(Task $task): bool
    {
        $cronExpression = new CronExpression($task->getCronExpression());

        return $cronExpression->isDue();
    }
}
