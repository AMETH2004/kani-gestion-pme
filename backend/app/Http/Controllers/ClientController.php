<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Support\Numerotation;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($terme = $request->string('recherche')->trim()->value()) {
            $query->where(function ($q) use ($terme) {
                $q->where('nom', 'like', "%{$terme}%")
                    ->orWhere('code', 'like', "%{$terme}%")
                    ->orWhere('telephone', 'like', "%{$terme}%")
                    ->orWhere('email', 'like', "%{$terme}%");
            });
        }

        if ($request->boolean('actifs_seulement')) {
            $query->where('actif', true);
        }

        return response()->json($query->orderBy('nom')->paginate($request->integer('par_page', 20)));
    }

    public function show(Client $client)
    {
        return response()->json(
            $client->append('solde')->loadCount(['factures', 'devis'])
        );
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();
        $data['code'] ??= Numerotation::prochain(Client::class, 'CLI', 'code');

        $client = Client::create($data);

        return response()->json([
            'message' => 'Client créé.',
            'data' => $client,
        ], 201);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return response()->json([
            'message' => 'Client mis à jour.',
            'data' => $client,
        ]);
    }

    public function destroy(Client $client)
    {
        if ($client->factures()->exists() || $client->devis()->exists()) {
            $client->update(['actif' => false]);

            return response()->json([
                'message' => 'Client lié à des documents : il a été désactivé plutôt que supprimé.',
            ]);
        }

        $client->delete();

        return response()->json(['message' => 'Client supprimé.']);
    }
}
