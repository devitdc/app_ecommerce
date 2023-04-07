<?php

namespace App\Class;

use App\Entity\Category;

class Search
{
    /**
     * @var string|null
     */
    public ?string $string = '';

    /**
     * @var Category[]
     */
    public array $categories = [];
}