<?php

namespace Tests\BotMan;

use Illuminate\Support\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Category;
use App\Spending;

class RecordsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_check_his_records_on_this_month()
    {
        $category = factory(Category::class)->create([
            'name' => 'Food',
            'icon' => 'food'
        ]);
        $icon = config("icons.{$category->icon}", '💵');

        $spending = factory(Spending::class)->create([
            'category_id' => $category->id,
        ]);

        $this->bot
            ->setUser([
                'id' => $spending->telegram_id,
            ])
            ->receives('records')
            ->assertReply("{$icon} {$category->name}: {$spending->amountFormatted}€");
    }
}
