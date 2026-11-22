<?php

namespace App\Http\Controllers\Beheer;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Nest;
use App\Models\Organisation;
use App\Models\User;
use App\Notifications\AccountInvite;
use App\Support\Money;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BeheerController extends Controller
{
    public function index()
    {
        return view('beheer.index', [
            'gemeenten' => Organisation::gemeenten()->withCount('users')->orderBy('name')->get(),
            'bestrijders' => Organisation::bestrijders()->withCount('users')->orderBy('name')->get(),
            'contracts' => Contract::with('gemeente', 'bestrijder')->latest()->get(),
            'unrouted' => Nest::whereNull('gemeente_id')->latest()->limit(50)->get(),
            'stats' => [
                'nesten' => Nest::count(),
                'open' => Nest::whereIn('status', \App\Support\NestStatus::OPEN)->count(),
                'gebruikers' => User::count(),
            ],
        ]);
    }

    private function orgRules(?Organisation $org = null): array
    {
        return [
            'type' => [$org ? 'prohibited' : 'required', 'in:gemeente,bestrijder'],
            'name' => ['required', 'string', 'max:190'],
            'cbs_code' => ['nullable', 'regex:/^GM\d{4}$/', Rule::unique('organisations', 'cbs_code')->ignore($org?->id)],
            'kvk' => ['nullable', 'string', 'max:20'],
            'vat_number' => ['nullable', 'string', 'max:30'],
            'iban' => ['nullable', 'string', 'max:40'],
            'invoice_email' => ['nullable', 'email'],
            'notify_email' => ['nullable', 'email'],
            'address' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function storeOrganisation(Request $request)
    {
        $data = $request->validate($this->orgRules(), ['cbs_code.regex' => 'CBS-code is GM gevolgd door 4 cijfers, bijv. GM0777.']);
        if ($data['type'] === 'gemeente' && empty($data['cbs_code'])) {
            return back()->withErrors(['cbs_code' => 'Een gemeente heeft een CBS-code nodig.'])->withInput();
        }
        $org = Organisation::create($data);
        if ($org->isGemeente()) {
            // Eerdere meldingen in deze gemeente die nog nergens heen konden, nu koppelen.
            Nest::whereNull('gemeente_id')->where('cbs_code', $org->cbs_code)->update(['gemeente_id' => $org->id]);
        }
        return redirect()->route('beheer.organisaties.show', $org)->with('status', 'Organisatie aangemaakt.');
    }

    public function showOrganisation(Organisation $organisation)
    {
        $organisation->load('users');
        $contracts = Contract::with('gemeente', 'bestrijder')->where('gemeente_id', $organisation->id)->orWhere('bestrijder_id', $organisation->id)->get();
        return view('beheer.organisatie', ['org' => $organisation, 'contracts' => $contracts, 'roles' => Role::LABELS]);
    }

    public function updateOrganisation(Request $request, Organisation $organisation)
    {
        $data = $request->validate($this->orgRules($organisation));
        $data['active'] = $request->boolean('active');
        $organisation->update($data);
        return back()->with('status', 'Opgeslagen.');
    }

    public function storeUser(Request $request, Organisation $organisation)
    {
        $roles = $organisation->isGemeente() ? [Role::GEMEENTE] : [Role::BESTRIJDER_EIGENAAR, Role::BESTRIJDER_MEDEWERKER];
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in($roles)],
        ]);
        $user = User::create($data + ['password' => Str::random(40), 'organisation_id' => $organisation->id]);
        $user->notify(new AccountInvite(Password::broker()->createToken($user)));
        return back()->with('status', 'Gebruiker aangemaakt en uitgenodigd per e-mail.');
    }

    public function toggleUser(User $user)
    {
        abort_if($user->id === auth()->id(), 422);
        $user->update(['active' => ! $user->active]);
        return back()->with('status', $user->active ? 'Account geactiveerd.' : 'Account geblokkeerd.');
    }

    public function storeContract(Request $request)
    {
        $data = $request->validate([
            'gemeente_id' => ['required', Rule::exists('organisations', 'id')->where('type', 'gemeente')],
            'bestrijder_id' => ['required', Rule::exists('organisations', 'id')->where('type', 'bestrijder')],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'standard_rate' => ['required', 'string'],
            'surcharge_height' => ['nullable', 'string'],
            'surcharge_urgent' => ['nullable', 'string'],
            'callout' => ['nullable', 'string'],
            'payment_days' => ['required', 'integer', 'between:7,90'],
        ]);
        Contract::create([
            'gemeente_id' => $data['gemeente_id'],
            'bestrijder_id' => $data['bestrijder_id'],
            'valid_from' => $data['valid_from'],
            'valid_until' => $data['valid_until'] ?? null,
            'standard_rate_cents' => Money::parse($data['standard_rate']),
            'surcharge_height_cents' => Money::parse($data['surcharge_height'] ?? '0'),
            'surcharge_urgent_cents' => Money::parse($data['surcharge_urgent'] ?? '0'),
            'callout_cents' => Money::parse($data['callout'] ?? '0'),
            'payment_days' => $data['payment_days'],
        ]);
        return back()->with('status', 'Contract vastgelegd.');
    }

    public function routeNest(Request $request, Nest $nest)
    {
        $data = $request->validate(['gemeente_id' => ['required', Rule::exists('organisations', 'id')->where('type', 'gemeente')]]);
        $nest->update(['gemeente_id' => $data['gemeente_id']]);
        return back()->with('status', 'Nest #' . $nest->id . ' toegewezen.');
    }
}
