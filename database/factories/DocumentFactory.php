<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for church document test records.
 *
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => '교회 문서 '.fake()->date('Y-m-d'),
            'file_path' => 'documents/'.Str::uuid().'.pdf',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
