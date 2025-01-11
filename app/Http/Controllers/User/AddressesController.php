<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DefaultAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressesController extends Controller
{
    public function index(Request $request): Response
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();

        return Inertia::render('User/Addresses/Index', compact('addresses'));
    }

    public function updateDefaultAddress(DefaultAddressRequest $request): void
    {
        // We use "get()" instead of "first()" just in case there are more than one.
        $addresses = Address::where([
            'user_id' => $request->user()->id,
            "default_{$request->get('type')}" => true,
        ])->get(['id', 'default_shipping', 'default_billing']);

        foreach ($addresses as $address) {
            $address->{"default_{$request->get('type')}"} = false;
            $address->save();
        }

        $address = Address::find($request->get('id'));
        $address->{"default_{$request->get('type')}"} = $request->get('value');
        $address->save();
    }
}
