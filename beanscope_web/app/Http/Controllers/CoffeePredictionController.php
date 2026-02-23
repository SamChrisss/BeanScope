<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Penting untuk memanggil API

class CoffeePredictionController extends Controller
{
    public function index()
    {
        return view('predict'); // Tampilan form upload
    }

    public function predict(Request $request)
    {
        // 1. Validasi file
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $image = $request->file('image');

        try {
            // 2. Kirim gambar ke FastAPI menggunakan Http Client Laravel
            $response = Http::attach(
                'file', // Nama field harus sesuai dengan FastAPI (file: UploadFile)
                file_get_contents($image),
                $image->getClientOriginalName()
            )->post(env('FASTAPI_URL') . '/predict');

            if ($response->successful()) {
                $result = $response->json();
                
                // Format all_predictions dari API menjadi array sederhana untuk view
                $all_scores = [];
                foreach ($result['all_predictions'] as $label => $data) {
                    $all_scores[$label] = $data['percentage'];
                }
                
                return view('predict', [
                    'result' => $result['prediction'], // Data dari FastAPI
                    'all_scores' => $all_scores, // Semua 5 kelas dengan persentase
                    'image_path' => $this->saveImageLocal($image) // Untuk menampilkan preview
                ]);
            }

            return back()->with('error', 'Gagal terhubung ke server AI.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function saveImageLocal($image)
    {
        $name = time().'.'.$image->extension();
        $image->move(public_path('uploads'), $name);
        return 'uploads/'.$name;
    }
}