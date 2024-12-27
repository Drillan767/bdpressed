<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    // public function __invoke(EmailVerificationRequest $request): RedirectResponse
    public function __invoke(Request $request): RedirectResponse
    {
        Log::debug('Accessed to VerifyEmailController::__invoke method');
        $redirectTo = $request->user()->hasRole('admin') ? 'admin.dashboard' : 'user.dashboard';

        $userId = hash_equals((string) $request->user()->getKey(), (string) $request->route('id'));
        $emailSha1 = sha1($request->user()->getEmailForVerification());
        $signatureMatches = hash_equals(sha1($request->user()->getEmailForVerification()), (string) $request->route('hash'));

        Log::debug($userId);
        Log::debug($emailSha1);
        Log::debug($request->route('hash'));
        Log::debug($signatureMatches);

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route($redirectTo, absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route($redirectTo, absolute: false).'?verified=1');
    }
}
