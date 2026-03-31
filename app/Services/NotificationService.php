<?php

namespace App\Services;

use App\Models\Card;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function notify(User $user, string $type, string $title, ?string $body = null, ?array $data = null, $notifiable = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'created_at' => now(),
        ]);
    }

    public static function notifyCardMembers(Card $card, string $type, string $title, ?string $body = null, ?int $exceptUserId = null): void
    {
        $card->load('members');
        foreach ($card->members as $member) {
            if ($exceptUserId && $member->id === $exceptUserId) continue;
            static::notify($member, $type, $title, $body, [
                'card_id' => $card->id,
                'board_id' => $card->board_id,
                'card_title' => $card->title,
            ], $card);
        }
    }

    public static function notifyCardWatchers(Card $card, string $type, string $title, ?string $body = null, ?int $exceptUserId = null): void
    {
        $card->load('watchers');
        $notifiedIds = [];

        foreach ($card->watchers as $watcher) {
            if ($exceptUserId && $watcher->id === $exceptUserId) continue;
            if (in_array($watcher->id, $notifiedIds)) continue;
            static::notify($watcher, $type, $title, $body, [
                'card_id' => $card->id,
                'board_id' => $card->board_id,
                'card_title' => $card->title,
            ], $card);
            $notifiedIds[] = $watcher->id;
        }
    }

    public static function notifyCardStakeholders(Card $card, string $type, string $title, ?string $body = null, ?int $exceptUserId = null): void
    {
        $card->load('members', 'watchers');
        $notifiedIds = [];

        $users = $card->members->merge($card->watchers);
        foreach ($users as $user) {
            if ($exceptUserId && $user->id === $exceptUserId) continue;
            if (in_array($user->id, $notifiedIds)) continue;
            static::notify($user, $type, $title, $body, [
                'card_id' => $card->id,
                'board_id' => $card->board_id,
                'card_title' => $card->title,
            ], $card);
            $notifiedIds[] = $user->id;
        }
    }

    /**
     * Parse @mentions from text. Matches @name against user names (case-insensitive).
     * Also tries matching against the username part of emails.
     */
    public static function parseMentions(string $text): array
    {
        // Match @word patterns (supports dots and hyphens in names)
        preg_match_all('/@([\w.\-]+)/', $text, $matches);
        if (empty($matches[1])) return [];

        $mentionNames = array_unique($matches[1]);
        $userIds = [];

        foreach ($mentionNames as $name) {
            // Try exact name match (case-insensitive), then try email prefix match
            $user = User::where('name', 'like', $name)->first()
                ?? User::where('name', 'like', str_replace('.', ' ', $name))->first()
                ?? User::where('email', 'like', $name . '@%')->first();

            if ($user) {
                $userIds[] = $user->id;
            }
        }

        return array_unique($userIds);
    }

    public static function notifyMentions(string $text, Card $card, User $mentioner): void
    {
        $userIds = static::parseMentions($text);
        foreach ($userIds as $userId) {
            if ($userId === $mentioner->id) continue;
            $user = User::find($userId);
            if (!$user) continue;
            static::notify($user, 'mention', "{$mentioner->name} mentioned you", "In card \"{$card->title}\"", [
                'card_id' => $card->id,
                'board_id' => $card->board_id,
                'card_title' => $card->title,
                'mentioned_by' => $mentioner->name,
            ], $card);
        }
    }
}
