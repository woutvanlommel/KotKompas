<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channel Authorization
|--------------------------------------------------------------------------
|
| SECURITY LAYERS FOR WEBSOCKET CHANNELS:
|
| 1. AUTHENTICATION — Broadcast::routes() is protected by the 'auth'
|    middleware, meaning unauthenticated users are rejected before they
|    can even attempt to join a channel.
|
| 2. RATE LIMITING — The 'throttle:broadcasting' limiter (defined in
|    bootstrap/app.php) caps auth attempts at 30 per minute per user or
|    IP address, preventing brute-force enumeration of conversation IDs.
|
| 3. PRIVATE CHANNELS — All chat channels are private. The client must
|    pass a signed auth request through the server before Laravel/Reverb
|    will issue a valid socket token. Public channels are never used.
|
| 4. OWNERSHIP VERIFICATION — The channel callback below queries the
|    database to confirm the authenticated user is an actual participant
|    of the requested conversation (either as tenant or landlord).
|    No user can access another user's conversation, even with a valid
|    session, even if they correctly guess the conversation ID.
|
*/

Broadcast::routes(['middleware' => ['web', 'auth', 'throttle:broadcasting']]);

/*
 | Channel: conversation.{conversationId}
 |
 | Only grants access if the authenticated user is either the tenant or
 | the landlord linked to this conversation in the database.
 | Returns false for any other authenticated user — Reverb will reject
 | the socket subscription with a 403.
 */
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    return Conversation::where('id', $conversationId)
        ->where(function ($query) use ($user) {
            $query->where('tenant_id', $user->id)
                ->orWhere('landlord_id', $user->id);
        })
        ->exists();
});
