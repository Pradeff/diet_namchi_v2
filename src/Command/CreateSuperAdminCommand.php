<?php
// src/Command/CreateSuperAdminCommand.php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-super-admin',
    description: 'Create a super admin user',
)]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'User full name')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'User password')
            ->addOption('phone', null, InputOption::VALUE_OPTIONAL, 'User phone number')
            ->addOption('address', null, InputOption::VALUE_OPTIONAL, 'User address')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('name')) {
            $name = $io->ask('Enter full name');
            $input->setOption('name', $name);
        }

        if (!$input->getOption('email')) {
            $email = $io->ask('Enter email address');
            $input->setOption('email', $email);
        }

        if (!$input->getOption('password')) {
            $password = $io->askHidden('Enter password (input will be hidden)');
            $input->setOption('password', $password);
        }

        if (!$input->getOption('phone')) {
            $phone = $io->ask('Enter phone number (optional)', null);
            $input->setOption('phone', $phone);
        }

        if (!$input->getOption('address')) {
            $address = $io->ask('Enter address (optional)', null);
            $input->setOption('address', $address);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getOption('name');
        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $phone = $input->getOption('phone');
        $address = $input->getOption('address');

        // Validate required fields
        if (empty($name)) {
            $io->error('Name is required.');
            return Command::FAILURE;
        }

        if (empty($email)) {
            $io->error('Email is required.');
            return Command::FAILURE;
        }

        if (empty($password)) {
            $io->error('Password is required.');
            return Command::FAILURE;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid email format.');
            return Command::FAILURE;
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error(sprintf('User with email "%s" already exists.', $email));
            return Command::FAILURE;
        }

        // Validate password strength
        if (strlen($password) < 6) {
            $io->error('Password must be at least 6 characters long.');
            return Command::FAILURE;
        }

        // Create the super admin user
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setAddress($address);
        $user->setIsActive(true);
        $user->setRoles(['ROLE_ADMIN']);

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success(sprintf(
                'Super admin user "%s" (%s) created successfully!',
                $user->getName(),
                $user->getEmail()
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error creating user: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
