<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BoardEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $boardId,
        public string $type,
        public array $payload,
        public ?int $userId = null,
    ) {
        $this->userId = $userId ?? auth()->id();
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('board.' . $this->boardId)];
    }

    public function broadcastAs(): string
    {
        return $this->type;
    }

    public function broadcastWith(): array
    {
        return [
            ...$this->payload,
            'user_id' => $this->userId,
        ];
    }
}
