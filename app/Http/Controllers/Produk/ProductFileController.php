<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Berkas produk siswa tersimpan privat; hanya pemilik, guru kelasnya,
 * peneliti, dan admin yang boleh membukanya.
 */
class ProductFileController extends Controller
{
    public function __invoke(Request $request, Product $product): StreamedResponse
    {
        $user = $request->user();
        $bolehGuru = $user->punyaPeran('guru') && $product->user->enrollments()
            ->whereHas('classroom', fn ($q) => $q->where('guru_id', $user->getKey()))->exists();

        abort_unless(
            $user->is($product->user) || $bolehGuru || $user->punyaPeran('peneliti', 'admin'),
            403,
        );
        abort_if($product->berkas === null || ! Storage::disk('local')->exists($product->berkas), 404);

        return Storage::disk('local')->response($product->berkas);
    }
}
