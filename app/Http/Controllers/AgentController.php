<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AgentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = User::where('role', 'agent')->with('handledDevices');

        // Jika yang login adalah Admin, hanya tampilkan agen miliknya
        if ($user->isAdmin()) {
            $query->where('parent_id', $user->id);
        }

        $agents = $query->latest()->paginate(10);
        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        $user = auth()->user();

        $deviceQuery = Device::where('status', 'connected');

        // Jika yang login Admin, hanya tampilkan device miliknya untuk dipilih
        if ($user->isAdmin()) {
            $deviceQuery->where('user_id', $user->id);
        }

        $devices = $deviceQuery->get();
        return view('agents.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:4'],
            'devices' => ['array'],
        ]);

        $agent = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'agent',
            'parent_id' => auth()->id(), // Tag ke Admin pembuatnya
        ]);

        if ($request->has('devices')) {
            $agent->handledDevices()->sync($request->devices);
        }

        return redirect()->route('agents.index')->with('success', 'Agen berhasil ditambahkan.');
    }

    public function edit(User $agent)
    {
        // Keamanan: Pastikan Admin tidak mengedit agen milik orang lain
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        return view('agents.edit', compact('agent'));
    }

    public function update(Request $request, User $agent)
    {
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', 'min:4'],
        ]);

        $agent->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('agents.index')->with('success', 'Password Agen ' . $agent->name . ' berhasil diubah.');
    }

    public function destroy(User $agent)
    {
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        $agent->delete();
        return redirect()->route('agents.index')->with('success', 'Agen berhasil dihapus.');
    }
}
