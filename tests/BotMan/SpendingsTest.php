<?php

namespace Tests\BotMan;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SpendingsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_get_help_on_bot_start()
    {
        $replies = [
            __('bot.add_spending'),
            __('bot.check_records'),
        ];

        $this->bot
            ->receives('/start')
            ->assertReply(__('bot.welcome'))
            ->assertReply(implode(PHP_EOL, $replies));
    }

    /** @test */
    public function user_can_register_a_spending()
    {
        $category = factory(\App\Category::class)->create([
            'name' => 'Food',
            'icon' => 'food',
        ]);

        $this->bot
            ->receives('5 Mercadona')
            ->assertQuestion(__('spending.category'))
            ->receivesInteractiveMessage($category->id)
            ->assertQuestion(__('spending.when', ['date' => Carbon::now()->format('d-m-Y')]))
            ->receivesInteractiveMessage('now')
            ->assertReply(__('spending.saved', [
                'amount'   => '5.00',
                'category' => $category->name,
            ]));
    }

    /** @test */
    public function user_can_register_a_spending_with_custom_date()
    {
        $category = factory(\App\Category::class)->create([
            'name' => 'Travel',
            'icon' => 'travel',
        ]);

        $this->bot
            ->receives('4.00 Mercadona')
            ->assertQuestion(__('spending.category'))
            ->receivesInteractiveMessage($category->id)
            ->assertQuestion(__('spending.when', ['date' => Carbon::now()->format('d-m-Y')]))
            ->receives('22-12-2017')
            ->assertReply(__('spending.saved', [
                'amount'   => '4.00',
                'category' => $category->name,
            ]));

        $this->assertDatabaseHas('spendings', [
            'created_at' => '2017-12-22 00:00:00',
        ]);
    }
}
