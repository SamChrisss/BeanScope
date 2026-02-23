<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BatchTestController extends Controller
{
    public function index()
    {
        return view('batch-test');
    }

    public function process(Request $request)
    {
        $request->validate([
            'zipfile' => 'required|file|mimes:zip|max:102400', // max 100MB
        ]);

        $zipFile = $request->file('zipfile');

        try {
            $response = Http::timeout(300)
                ->attach(
                    'file',
                    file_get_contents($zipFile->getRealPath()),
                    $zipFile->getClientOriginalName(),
                    ['Content-Type' => 'application/zip']
                )
                ->post(env('FASTAPI_URL') . '/predict-batch');

            if ($response->successful()) {
                $data = $response->json();
                return view('batch-test', [
                    'batchResult' => $data,
                ]);
            }

            $errorMsg = $response->json('detail') ?? 'Gagal terhubung ke server AI.';
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
