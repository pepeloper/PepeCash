<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use BotMan\BotMan\BotMan;
use App\Category;

class RecordsController extends Controller
{
    public function show(BotMan $bot)
    {
        $c = Category::all()
            ->load(['spendings' => function ($query) use ($bot) {
                $query->where('telegram_id', $bot->getUser()->getId());
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ]);
            }])
            ->mapWithKeys(function ($item) {
                $value = $item->spendings->reduce(function ($carry, $item) {
                    return $item->amountFormatted;
                });
                $icon = config("icons.{$item->icon}", '💵');

                return ["{$icon} {$item->name}" => $value];
            })
            ->each(function ($item, $key) use ($bot) {
                $bot->reply("{$key}: {$item}€");
            });

        $total = $c->reduce(function ($carry, $item) {
            return $carry + $item;
        }, 0);

        $bot->reply(__('spending.total_month', ['amount' => $total]));
    }
}
