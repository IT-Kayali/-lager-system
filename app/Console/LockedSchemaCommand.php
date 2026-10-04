<?php

namespace App\Console;

use App\Services\SystemBackupService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LockedSchemaCommand extends Command
{
    public function __construct(
        private Command $command,
        private SystemBackupService $backups
    ) {
        parent::__construct();

        $this->setName($command->getName());
        $this->setDescription($command->getDescription());
        $this->setHelp($command->getHelp());
        $this->setAliases($command->getAliases());
        $this->setDefinition(clone $command->getDefinition());
        $this->setHidden($command->isHidden());
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->command->setLaravel($this->getLaravel());
        $this->command->setApplication($this->getApplication());

        return $this->backups->withMigrationLock(
            fn (): int => $this->command->run($input, $output)
        );
    }
}
