<?php

namespace Tests\BotMan;

use App\Category;
use App\Spending;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class RecordsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_check_his_records_on_this_month()
    {
        $category = factory(Category::class)->create([
            'name' => 'Food',
            'icon' => 'food',
        ]);
        $icon = config("icons.{$category->icon}", '💵');

        $spending = factory(Spending::class)->create([
            'category_id' => $category->id,
        ]);

        $spending = factory(Spending::class)->create([
            'category_id' => $category->id,
            'created_at' => Carbon::now()->endOfMonth(),
        ]);

        $this->bot
            ->setUser([
                'id' => $spending->telegram_id,
            ])
            ->receives('records')
            ->assertReply("{$icon} {$category->name}: {$spending->amountFormatted}€");
    }

    /** @test */
    public function user_can_check_his_records_by_category()
    {
        $category = factory(Category::class)->create([
            'name' => 'Tech',
            'icon' => 'tech',
        ]);

        $icon = config("icons.{$category->icon}", '💵');

        $spending = factory(Spending::class)->create([
            'category_id' => $category->id,
        ]);

        $this->bot
            ->setUser([
                'id' => $spending->telegram_id,
            ])
            ->receives('records Tech')
            ->assertReply(__('spending.show', [
                'id' => $spending->id,
                'amount' => $spending->amountFormatted,
                'concept' => $spending->concept,
                'date' => $spending->created_at->format('d/m/Y'),
            ]));
    }
}
