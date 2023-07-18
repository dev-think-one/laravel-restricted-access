<?php

namespace LinkRestrictedAccess\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LinkRestrictedAccess\Models\RestrictedLink;
use LinkRestrictedAccess\Models\RestrictedLinkOpenAction;

class OpenDocumentController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var RestrictedLink $restrictedLink */
        $restrictedLink = \LinkRestrictedAccess\RestrictedAccess::restrictedLinkModel()::query()
            ->where('uuid', $request->route('uuid'))
            ->firstOrFail();

        $request->validate([
            'pin' => [
                'nullable',
                Rule::requiredIf($restrictedLink->checkPin),
                'string',
                'max:100',
            ],
            'name' => [
                'nullable',
                Rule::requiredIf($restrictedLink->checkName),
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                Rule::requiredIf($restrictedLink->checkEmail),
                'email',
                'max:255',
            ],
        ]);

        if (
            $restrictedLink->checkPin &&
            (
                !$restrictedLink->pin ||
                $restrictedLink->pin != $request->input('pin')
            )

        ) {
            throw ValidationException::withMessages([
                'pin' => __('Incorrect pin'),
            ]);
        }

        /** @var RestrictedLinkOpenAction $openAction */
        $openAction = $restrictedLink->openActions()->make([
            'viewed_at' => Carbon::now(),
        ])->fillBrowserFingerPrint($request);


        if ($user = $request->user()) {
            $openAction->viewer()->associate($user);
        }

        if ($restrictedLink->checkPin) {
            $openAction->verification_result->setAttribute('pin_checked', true);
        }

        if ($restrictedLink->checkName) {
            $openAction->verification_result->setAttribute('name', $request->input('name'));
        }

        if ($restrictedLink->checkEmail) {
            $openAction->verification_result->setAttribute('email', $request->input('email'));
        }

        $openAction->save();

        $message = __('Access allowed.');

        $cookie = Cookie::make($restrictedLink->cookieName(), $openAction->uuid, 60 * 24 /* 24 hours */);

        if ($request->expectsJson()) {
            return Response::json([
                'message' => $message,
                'data'    => [
                    'success' => true,
                ],
            ])->withCookie($cookie);
        }

        return Redirect::back()
            ->with(['message' => $message])
            ->withCookie($cookie);
    }
}
