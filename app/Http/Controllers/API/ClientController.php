<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::query()->latest('id')->paginate(15);
        return response()->json($clients);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:150'],
            'email' => ['required','email','max:150', Rule::unique('clients','email')],
            'phone' => ['nullable','string','max:50'],
        ]);

        $client = Client::create($data);
        return response()->json($client, Response::HTTP_CREATED);
    }

    public function show(Client $client)
    {
        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'  => ['sometimes','required','string','max:150'],
            'email' => ['sometimes','required','email','max:150', Rule::unique('clients','email')->ignore($client->id)],
            'phone' => ['nullable','string','max:50'],
        ]);

        $client->update($data);
        return response()->json($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
