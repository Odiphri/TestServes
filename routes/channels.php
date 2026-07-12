<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\LiveSupportConversation;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.SchoolOwner.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['school_owner']]);

Broadcast::channel('App.Models.PlatformAdmin.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['platform_admin']]);

Broadcast::channel('live-support.{conversationId}', function ($user, int $conversationId) {
    $conversation = LiveSupportConversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    if ($user instanceof \App\Models\PlatformAdmin) {
        return $user->isSuperAdmin()
            || $user->canPerform('support.view')
            || (int) $conversation->assigned_admin_id === (int) $user->id;
    }

    if ($user instanceof \App\Models\SchoolOwner) {
        return (int) $conversation->school_owner_id === (int) $user->id;
    }

    return false;
}, ['guards' => ['platform_admin', 'school_owner']]);
