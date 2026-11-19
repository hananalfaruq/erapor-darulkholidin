<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = Pengaturan::current();
        return view('admin.pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $pengaturan = Pengaturan::current();

        $validated = $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'logo'         => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $data = ['nama_sekolah' => $validated['nama_sekolah']];

        if ($request->hasFile('logo')) {
            if ($pengaturan->logo && $pengaturan->logo !== 'logo.png') {
                $oldPath = public_path($pengaturan->logo);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $filename = 'logo_' . time() . '.' . $request->file('logo')->extension();
            $request->file('logo')->move(public_path(), $filename);
            $data['logo'] = $filename;
        }

        $pengaturan->update($data);

        return back()->with('success', 'Pengaturan sekolah berhasil diperbarui.');
    }

    public function editPassword()
    {
        return view('admin.pengaturan.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:4', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min'       => 'Password baru minimal 4 karakter.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama yang Anda masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password Administrator berhasil diperbarui.');
    }
}