<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::all();
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        // 2. Create and save the client
        Client::create($validated);

        // 3. Redirect back to the client list
        return redirect()->route('clients.index')
                         ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        // 1. Validate the data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        // This email rule is special:
        // It must be unique, BUT it must ignore its own ID
        'email' => [
            'required',
            'email',
            'max:255',
            Rule::unique('clients')->ignore($client->id),
        ],
        'company_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
    ]);

    // 2. Update the client
    $client->update($validated);

    // 3. Redirect back to the client list
    return redirect()->route('clients.index')
                     ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // 1. Delete the client
        $client->delete();

        // 2. Redirect back
        return redirect()->route('clients.index')
                         ->with('success', 'Client deleted successfully.');
    }

    
}
