<?php

declare(strict_types=1);

namespace App\Adapter;

use Faker\Generator;

class AppAdapter
{
    /**
     * Генератор данных FakerPHP https://fakerphp.github.io/
     *
     * @return Generator Faker
     */
    public function getFaker(): Generator
    {
        return \Faker\Factory::create();
    }
}