<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class UnifiedNotificationService
{
    /**
     * Create a notification visible across all products.
     *
     * @param string      $product  Product key: 'board', 'projects', 'opportunity', 'hr', or 'system'
     * @param int         $userId   Recipient user ID
     * @param string      $title    Short notification title
     * @param string|null $body     Optional extended description
     * @param array       $data     Arbitrary payload (IDs, URLs, etc.)
     */
    public function notify(string $product, int $userId, string $title, ?string $body = null, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $product,
            'title'   => $title,
            'body'    => $body,
            'data'    => array_merge($data, ['product' => $product]),
        ]);
    }

    /**
     * Notify multiple users at once.
     */
    public function notifyMany(string $product, array $userIds, string $title, ?string $body = null, array $data = []): void
    {
        $payload = array_merge($data, ['product' => $product]);
        $now = now();

        $rows = array_map(fn ($uid) => [
            'user_id'    => $uid,
            'type'       => $product,
            'title'      => $title,
            'body'       => $body,
            'data'       => json_encode($payload),
            'created_at' => $now,
            'updated_at' => $now,
        ], $userIds);

        Notification::insert($rows);
    }

    /**
     * Get notifications for a user, optionally filtered by product.
     */
    public function getForUser(User $user, ?string $product = null, int $limit = 30): array
    {
        $query = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($product) {
            $query->where('type', $product);
        }

        $notifications = $query->limit($limit)->get();
        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return [
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ];
    }
}
