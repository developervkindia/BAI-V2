<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendDueReminders extends Command
{
    protected $signature = 'kanban:send-due-reminders';
    protected $description = 'Send notifications for cards with approaching due dates';

    public function handle(): int
    {
        $now = now();

        $cards = Card::whereNotNull('due_date')
            ->where('due_date_complete', false)
            ->where('is_archived', false)
            ->where('is_template', false)
            ->whereNotNull('due_reminder')
            ->where('due_reminder', '!=', 'none')
            ->with(['members', 'watchers'])
            ->get();

        $sent = 0;

        foreach ($cards as $card) {
            $reminderTime = match ($card->due_reminder) {
                'at_time' => $card->due_date,
                '5min' => $card->due_date->subMinutes(5),
                '1hour' => $card->due_date->subHour(),
                '1day' => $card->due_date->subDay(),
                default => null,
            };

            if (!$reminderTime) continue;

            // Check if reminder should fire (within the last hour window)
            if ($reminderTime->between($now->copy()->subHour(), $now)) {
                NotificationService::notifyCardStakeholders(
                    $card,
                    'due_date',
                    "Due date reminder: {$card->title}",
                    "Due " . $card->due_date->diffForHumans(),
                );
                $sent++;
            }
        }

        $this->info("Sent {$sent} due date reminders.");
        return Command::SUCCESS;
    }
}
