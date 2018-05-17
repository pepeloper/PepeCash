<?php

namespace App\Conversations;

use App\Category;
use App\Spending;
use BotMan\BotMan\Facades\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Carbon;

class AddSpending extends Conversation
{
    protected $spending;

    public function __construct($concept, $amount)
    {
        $this->spending = new Spending();
        $this->spending->concept = $concept;
        $this->spending->amount = $amount * 100;
        $this->spending->telegram_id = BotMan::getUser()->getId();
    }

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $buttons = Category::all()->map(function ($item) {
            return Button::create($item->name)->value($item->id);
        });

        // Ask for spending category.
        $question = Question::create(__('spending.category'))
            ->fallback(__('bot.failed_question'))
            ->callbackId('ask_reason')
            ->addButtons($buttons->toArray());

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                $this->spending->category()->associate(Category::find($answer->getValue()));
                $this->askDate();
            }
        });
    }

    public function askDate()
    {
        $question = Question::create(__('spending.when', ['date' => Carbon::now()->format('d-m-Y')]))
            ->fallback(__('bot.failed_question'))
            ->callbackId('ask_reason')
            ->addButtons([
                Button::create(__('spending.yesterday'))->value('yesterday'),
                Button::create(__('spending.today'))->value('now'),
            ]);

        $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                $date = ($answer->getValue() == 'yesterday') ? Carbon::yesterday() : Carbon::now();
            } else {
                $date = Carbon::parse($answer->getValue());
            }

            $this->spending->created_at = $date;
            $this->spending->save();

            $this->say(__('spending.saved', [
                'amount'   => $this->spending->amountFormatted,
                'category' => $this->spending->category->name,
            ]));
        });
    }
}
