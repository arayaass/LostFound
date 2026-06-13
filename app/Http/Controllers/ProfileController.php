<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request) { return view('profile', ['user' => $request->user()->load('items')]); }
    public function update(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:100', 'email' => 'required|email|unique:users,email,'.$request->user()->id, 'avatar_file' => 'nullable|image|max:3072']);
        if ($request->hasFile('avatar_file')) $data['avatar'] = asset('storage/'.$request->file('avatar_file')->store('avatars', 'public'));
        $request->user()->update($data);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
