<?php

namespace App\Http\Controllers\Bestrijder;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountInvite;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $org = $this->org()->load('users', 'contractsAsBestrijder.gemeente');
        return view('bestrijder.instellingen', compact('org'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'kvk' => ['nullable', 'string', 'max:20'],
            'vat_number' => ['nullable', 'string', 'max:30'],
            'iban' => ['nullable', 'string', 'max:40'],
            'invoice_email' => ['nullable', 'email'],
            'notify_email' => ['nullable', 'email'],
            'vat_rate' => ['required', 'integer', 'in:0,9,21'],
            'base_lat' => ['nullable', 'numeric', 'between:50.7,53.6'],
            'base_lng' => ['nullable', 'numeric', 'between:3.3,7.3'],
        ]);
        $this->org()->update($data);
        return back()->with('status', 'Gegevens opgeslagen.');
    }

    public function addUser(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:190'], 'email' => ['required', 'email', 'unique:users,email']]);
        $user = User::create($data + ['password' => Str::random(40), 'role' => Role::BESTRIJDER_MEDEWERKER, 'organisation_id' => $this->org()->id]);
        $user->notify(new AccountInvite(Password::broker()->createToken($user)));
        return back()->with('status', 'Medewerker uitgenodigd.');
    }

    public function toggleUser(User $user)
    {
        abort_unless($user->organisation_id === $this->org()->id && $user->id !== auth()->id(), 404);
        $user->update(['active' => ! $user->active]);
        return back()->with('status', $user->active ? 'Account geactiveerd.' : 'Account geblokkeerd.');
    }
}
