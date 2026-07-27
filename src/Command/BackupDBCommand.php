<?php

namespace App\Command;

use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument; // для аргумента email
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:backup:db')]
class BackupDBCommand extends Command
{
    public function __construct()//NotificationService $notificationService)
    {
        parent::__construct();
        //$this->notificationService = $notificationService;
    }
//    public function __construct()//private EntityManagerInterface $em)
//    {
//        parent::__construct();
//    }

    protected function configure(): void
    {
        $this
//            ->addArgument('db_name', InputArgument::REQUIRED, 'Название БД');
            ->addOption('db_name', null, InputOption::VALUE_NONE, 'Название БД');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $io = new SymfonyStyle($input, $output);

//        $io->error('Бэкап не удался');                // ❌ красный, с переносом
//        $io->success('Готово!');                     // ✅ зелёный
//        $io->note('Запустите docker compose up -d');  // 💡 подсказка с отступом
//        $io->title('Бэкап базы данных');              // заголовок с линиями

        # Конфигурация
        $backupDir = dirname(__DIR__, 2) . '/backups'; // ../../backups от папки Command
        //$backupDir="./backups";
        $dbName=$input->getOption('db_name') ?? "symfony_db";
        $dbUser="root";
        $url = parse_url($_ENV['DATABASE_URL']);
        $dbPassword = $url['pass'] ?? null;
        //$dbPassword="password";
//        $dbPassword = $_ENV['DB_PASSWORD'] ?? null;
        if (!$dbPassword) {
//            $output->writeln('Переменная DB_PASSWORD не задана!');
            $io->error('Переменная DB_PASSWORD не задана!');
            return Command::FAILURE;
        }
        $containerName="my_first_project-database-1";  # или название твоего контейнера

        # Проверяем, что докер запущен и контейнер существует
        // --- ПРОВЕРКА через docker inspect ---
        $inspect = new Process([
            'docker', 'inspect',
            '--format', '{{.State.Running}}',
            $containerName,
        ]);
        $inspect->run();

        if (!$inspect->isSuccessful()) {
            // Контейнер не найден ИЛИ ошибка docker
            $io->error("Контейнер $containerName не найден или не запущен.");
            $io->note('Проверьте: docker ps или запустите: docker compose up -d');
            return Command::FAILURE;
        }

        $isRunning = trim($inspect->getOutput());
        if ($isRunning !== 'true') {
            $io->error("Контейнер $containerName остановлен (состояние: $isRunning).");
            $io->note('Запустите: docker compose up -d');
            return Command::FAILURE;
        }
        // ------------------------------------


        # Генерируем имя файла с датой и временем
        $date=date("Y-m-d_H-i-s");
        $backupFile=$backupDir . "/" . $dbName . "_" . "$date.sql.gz";

        // Создаём директорию для бэкапов
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        //mkdir(dirname($backupFile), 0755, true);

        $io->note("Создаю дамп базы данных $dbName...");
//        echo "Создаю дамп базы данных $dbName...";

//        $mysqldump  = new Process([
//            'docker', 'exec', $containerName, 'mysqldump',
//            '--single-transaction',
//            '-u', $dbUser,
//            $dbName
//        ]);
        // Пароль передаём через переменную окружения, чтобы не светить в аргументах
        //$mysqldump ->setEnv(['MYSQL_PWD' => $dbPassword]);

        $command = sprintf(
            'docker exec %s mysqldump --single-transaction -u %s %s | gzip > %s',
            escapeshellarg($containerName),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($backupFile)
        );

        $process = Process::fromShellCommandline($command);
        $process->setEnv(['MYSQL_PWD' => $dbPassword]);
        $process->setTimeout(300);
        $process->run();
//        $output = $process->getOutput();
        // Перенаправляем вывод в gzip, а потом в файл
//        $gzip = new Process(['gzip']);
//        $mysqldump ->pipeTo($gzip)->toFile($backupFile);

        $process->run();

        if (!$process->isSuccessful()) {
//            $output->writeln('Бэкап не удался: ' . $process->getErrorOutput());
            $io->error('Бэкап не удался: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }

//        $output->writeln("Бэкап готов: $backupFile");
        $io->success("Бэкап готов: $backupFile");
        return Command::SUCCESS;

    }
}
