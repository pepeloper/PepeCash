<?php

namespace App\Http\Controllers;

use App\Conversations\AddSpending;
use BotMan\BotMan\BotMan;

class SpendingController extends Controller
{
    public function saveSpending(BotMan $bot, string $amount, string $concept)
    {
        $bot->startConversation(new AddSpending($concept, $amount));
    }

    public function welcome(BotMan $bot)
    {
        $replies = [
            __('bot.add_spending'),
            __('bot.check_records'),
        ];

        $bot->reply(__('bot.welcome'));
        $bot->reply(implode(PHP_EOL, $replies));
    }
}
