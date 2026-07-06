<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Article
{
    public ?int $id = null; // Временное поле для хранения id

    /**
     * @Assert\NotBlank(message="Заголовок не может быть пустым!")
     * @Assert\Length(
     *     min=5,
     *     max=100,
     *     minMessage="Заголовок должен быть не короче 5 символов",
     *     maxMessage="Заголовок должен быть не длиннее 100 символов"
     * )
     */
    public string $title;

    /**
     * @Assert\NotBlank(message="Текст статьи обязателен!")
     */
    public string $content;
}
