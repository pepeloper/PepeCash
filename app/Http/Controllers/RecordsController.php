<?php

namespace App\Http\Controllers;

use App\Category;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Carbon;

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

    public function showByCategory(Botman $bot, $category_name)
    {
        $category = Category::where('name', $category_name)->get()->first();
        if (!$category) {
            return $bot->reply("Can't find a matching category for {$category_name}");
        }

        $response = [];
        $category->load(['spendings' => function ($query) use ($bot) {
            $query->where('telegram_id', $bot->getUser()->getId());
            $query->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]);
        }]);

        $category->spendings->each(function ($item, $key) use (&$response) {
            $response[] = __('spending.show', [
                'id'      => $item->id,
                'amount'  => $item->amountFormatted,
                'concept' => $item->concept,
                'date'    => $item->created_at->format('d/m/Y'),
            ]);
        });

        $bot->reply(implode(PHP_EOL, $response));
    }
}
