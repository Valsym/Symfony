<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument; // для аргумента email
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:user:create')]  // <-- Добавь эту строку
class CreateUserCommand extends Command
{
//    protected static $defaultName = 'app:user:create';

    public function __construct(private UserPasswordHasherInterface $hasher, private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Логин/Email пользователя')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Сделать администратором');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();

        $user->setEmail($input->getArgument('email'));
//        $user->setUsername($input->getArgument('username'));
        $user->setPassword($this->hasher->hashPassword($user, 'temp_password'));

        if ($input->getOption('admin')) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('Пользователь создан! ID: ' . $user->getId());
        return Command::SUCCESS;
    }
}
