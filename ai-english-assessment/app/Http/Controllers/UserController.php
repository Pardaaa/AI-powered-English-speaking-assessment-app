<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('name')->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['admin', 'dosen', 'mahasiswa'])],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'New user has been created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Tidak digunakan untuk saat ini
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {

        if ($user->id === Auth::id()) {
            abort(403, 'You cannot edit your own account from this page.');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['admin', 'dosen', 'mahasiswa'])],
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('users.index')->with('success', 'User has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Pengecekan keamanan
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User has been deleted successfully.');
    }


    public function showImportForm(): View
    {
        return view('users.import');
    }

    /**
     * Handle the import of users using native PHP (CSV Only).
     */
    public function importUsers(Request $request)
    {
        // 1. Validasi hanya menerima CSV
        $request->validate([
            'file_import' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file_import');

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $row = 0;
            $importedCount = 0;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row++;

                if ($row == 1) continue;

                if (count($data) < 4) continue;

                $name = $data[0];
                $email = $data[1];
                $password = $data[2];
                $role = strtolower($data[3]); 

                if (User::where('email', $email)->exists()) continue;
                if (!in_array($role, ['admin', 'dosen', 'mahasiswa'])) $role = 'mahasiswa'; 

                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => $role,
                ]);

                $importedCount++;
            }
            fclose($handle);

            return redirect()->route('users.index')->with('success', "$importedCount users imported successfully!");
        }

        return back()->with('error', 'Failed to read the file.');
    }
}
