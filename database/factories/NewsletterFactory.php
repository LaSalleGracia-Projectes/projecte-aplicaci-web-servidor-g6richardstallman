<?php

namespace Database\Factories;

use App\Models\Newsletter;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Newsletter>
 */
class NewsletterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = Newsletter::class;

     public function definition()
     {
         return [
             'spotNotification' => $this->faker->boolean,
             'fechaEvento' => $this->faker->dateTimeThisYear,
             'contenido' => $this->faker->paragraph(3),
             'idEvento' => Evento::inRandomOrder()->first()->idEvento
         ];
     }
 }