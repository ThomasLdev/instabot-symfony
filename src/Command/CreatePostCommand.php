<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Command;

use App\Entity\Task;
use App\Service\Tasks\TaskRunnerService;
use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsCommand(
    name: 'app:cron:create-posts',
    description: 'Create an instagram post based on google drive media, at a given time.',
)]
class CreatePostCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskRunnerService $taskRunnerService,
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
        $io = new SymfonyStyle($input, $output);

        $io->title('Lets make some instagram posts !');

        $io->section('Getting all tasks');
        $tasks = $this->getTasks();

        if (empty($tasks)) {
            $io->error('No tasks found');

            return Command::FAILURE;
        }

        /** @var Task $task */
        foreach ($io->progressIterate($tasks) as $task) {
            if (false === $task->getUser() instanceof UserInterface) {
                $this->entityManager->remove($task);

                continue;
            }

            //            if ($this->isTaskDue($task)) {
            $this->taskRunnerService->run($task);
            //            }
        }

        $io->success('All tasks have been processed. Check the logs for further details.');

        return Command::SUCCESS;
    }

    private function getTasks(): array
    {
        return $this->entityManager->getRepository(Task::class)->findTasksToRun();
    }

    private function isTaskDue(Task $task): bool
    {
        return (new CronExpression($task->getCronExpression()))->isDue();
    }
}
