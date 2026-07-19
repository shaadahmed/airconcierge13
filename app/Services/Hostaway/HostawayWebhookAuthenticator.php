<?php

namespace App\Services\Hostaway;

use Illuminate\Http\Request;

/**
 * Verifies Hostaway webhook Basic Auth credentials (Hostaway-native webhook security).
 *
 * @see docs/adr/008-hostaway-webhook-basic-auth.md
 */
final class HostawayWebhookAuthenticator
{
    public function verify(Request $request): bool
    {
        $expectedUsername = config('services.hostaway.webhook.username');
        $expectedPassword = config('services.hostaway.webhook.password');

        if (! is_string($expectedUsername) || $expectedUsername === ''
            || ! is_string($expectedPassword) || $expectedPassword === '') {
            return false;
        }

        $providedUsername = $request->getUser();
        $providedPassword = $request->getPassword();

        if (! is_string($providedUsername) || ! is_string($providedPassword)) {
            return false;
        }

        return hash_equals($expectedUsername, $providedUsername)
            && hash_equals($expectedPassword, $providedPassword);
    }
}
