<?php

namespace LinkRestrictedAccess\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use LinkRestrictedAccess\Models\RestrictedLink;
use LinkRestrictedAccess\RestrictedAccess;

class CheckPinController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var RestrictedLink $restrictedLink */
        $restrictedLink = RestrictedAccess::modelClass('link')::query()
            ->where('uuid', $request->route('uuid'))
            ->firstOrFail();

        $request->validate([
            'pin' => [
                'required',
                'string',
                'max:100',
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

        $message = __('Pin successful checked.');

        if ($request->expectsJson()) {
            return Response::json([
                'message' => $message,
                'data'    => [
                    'success' => true,
                ],
            ]);
        }

        return Redirect::back()->with(['message' => $message]);
    }
}
