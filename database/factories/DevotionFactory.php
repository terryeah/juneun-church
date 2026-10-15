<?php

namespace Database\Factories;

use App\Models\Devotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for 큐티 test records.
 *
 * @extends Factory<Devotion>
 */
class DevotionFactory extends Factory
{
    /**
     * Define the model's default state: today's day, published.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'devotion_date' => today(),
            'title' => '은혜를 보는 사람',
            'scripture' => '사도행전 11:22-24',
            'passage' => '바나바는 착한 사람이요 성령과 믿음이 충만한 사람이라',
            'content' => '<p>오늘 내가 발견한 하나님의 은혜는 무엇입니까?</p>',
            'is_published' => true,
        ];
    }
}
