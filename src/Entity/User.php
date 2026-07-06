<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class User
{
    /**
     * @Assert\NotBlank(message="email не может быть пустым!")
     * @Assert\Email(
     * message="Email '{{ value }}' не является валидным email адресом."
     * )
     */
    public string $email;

    /**
     * @Assert\Length(
     *      min=6,
     *      minMessage="password должен быть не короче 6 символов",
          *  )
     */
    public string $password;

    public string $agreeTerms;
}
