<?php

namespace App\Service;

use App\Entity\User;

interface EmailSenderInterface
{
    public function sendConfirmationEmail(User $user): void;
}

