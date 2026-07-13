<?php

namespace App\Support;

use App\Models\NotificationRecipient;
use App\Models\NotificationThread;
use App\Models\PlatformAdmin;
use App\Models\SchoolOwner;
use App\Models\User;
use App\Events\NotificationThreadMessageSent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class NotificationCenter
{
    public function currentRecipient(Request $request): ?Model
    {
        $admin = Auth::guard('platform_admin')->user();
        if ($admin instanceof PlatformAdmin) {
            return $admin;
        }

        $owner = Auth::guard('school_owner')->user();
        if ($owner instanceof SchoolOwner) {
            return $owner;
        }

        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function currentSchoolId(Request $request): ?int
    {
        if (app()->bound('currentSchool')) {
            return app('currentSchool')?->id;
        }

        $owner = Auth::guard('school_owner')->user();

        return $owner instanceof SchoolOwner ? $owner->school_id : null;
    }

    public function queryForCurrentUser(Request $request): Builder
    {
        $recipient = $this->currentRecipient($request);

        abort_unless($recipient, 403);

        return $this->queryFor($recipient, $this->currentSchoolId($request));
    }

    public function latestForCurrentUser(Request $request, int $limit = 5): Collection
    {
        return $this->queryForCurrentUser($request)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function unreadCountForCurrentUser(Request $request): int
    {
        return $this->queryForCurrentUser($request)
            ->whereNull('read_at')
            ->count();
    }

    public function markReadForCurrentUser(Request $request, NotificationRecipient $notification): void
    {
        $this->assertOwns($request, $notification);

        if (! $notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }
    }

    public function markAllReadForCurrentUser(Request $request): int
    {
        return $this->queryForCurrentUser($request)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function replyForCurrentUser(Request $request, NotificationRecipient $notification, string $message): void
    {
        $this->assertOwns($request, $notification);

        $campaign = $notification->campaign;

        if (! $campaign?->allows_replies || $campaign->is_system_notification) {
            throw ValidationException::withMessages(['message' => 'Replies are not enabled for this notification.']);
        }

        if ($campaign->expires_at && $campaign->expires_at->isPast()) {
            throw ValidationException::withMessages(['message' => 'This notification has expired.']);
        }

        $thread = NotificationThread::firstOrCreate(
            ['notification_recipient_id' => $notification->id],
            ['status' => 'open']
        );

        if ($thread->status === 'closed') {
            throw ValidationException::withMessages(['message' => 'This notification thread is closed.']);
        }

        $sender = $this->currentRecipient($request);

        $threadMessage = $thread->messages()->create([
            'sender_type' => $sender::class,
            'sender_id' => $sender->getKey(),
            'message' => $message,
        ]);

        try {
            broadcast(new NotificationThreadMessageSent($threadMessage))->toOthers();
        } catch (Throwable $exception) {
            Log::warning('Notification reply broadcast failed; reply was saved.', [
                'message_id' => $threadMessage->id,
                'error' => $exception->getMessage(),
            ]);
        }

        $this->markReadForCurrentUser($request, $notification);
    }

    public function hideForCurrentUser(Request $request, NotificationRecipient $notification): void
    {
        $this->assertOwns($request, $notification);

        $notification->forceFill(['owner_deleted_at' => now()])->save();
    }

    public function assertOwns(Request $request, NotificationRecipient $notification): void
    {
        $recipient = $this->currentRecipient($request);

        abort_unless($recipient, 403);

        $sameRecipient = $notification->notifiable_type === $recipient::class
            && (int) $notification->notifiable_id === (int) $recipient->getKey();

        abort_unless($sameRecipient, 403);

        if ($recipient instanceof User) {
            abort_unless((int) $notification->school_id === (int) $this->currentSchoolId($request), 403);
        }
    }

    private function queryFor(Model $recipient, ?int $schoolId): Builder
    {
        return NotificationRecipient::query()
            ->with(['campaign', 'thread.messages'])
            ->where('notifiable_type', $recipient::class)
            ->where('notifiable_id', $recipient->getKey())
            ->whereNull('owner_deleted_at')
            ->when($recipient instanceof User, fn (Builder $query) => $query->where('school_id', $schoolId))
            ->whereHas('campaign', function (Builder $query) {
                $query->where('status', 'sent')
                    ->where(function (Builder $expires) {
                        $expires->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            });
    }
}
