<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="API Penyimpanan Gambar",
 * description="Dokumentasi API untuk layanan penyimpanan dan pengambilan gambar."
 * )
 * @OA\SecurityScheme(
 * type="apiKey",
 * in="header",
 * name="X-API-Key",
 * securityScheme="ApiKeyAuth",
 * description="API Key untuk otentikasi."
 * )
 */
class ProductApiController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/product",
     * operationId="getProductsList",
     * tags={"Products"},
     * summary="Mengambil daftar gambar",
     * security={{"ApiKeyAuth":{}}},
     * description="Mengembalikan daftar semua gambar yang ada, dengan opsi pencarian berdasarkan judul.",
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Kata kunci untuk mencari gambar berdasarkan judul.",
     * required=false,
     * @OA\Schema(
     * type="string"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Operasi berhasil",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Product")
     * )
     * )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->latest()->get());
    }

    /**
     * @OA\Post(
     * path="/api/product",
     * operationId="storeImage",
     * tags={"Products"},
     * summary="Mengunggah gambar baru",
     * security={{"ApiKeyAuth":{}}},
     * description="Menyimpan file gambar baru beserta judulnya ke dalam sistem.",
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"title", "product"},
     * @OA\Property(property="title", type="string", description="Judul untuk gambar."),
     * @OA\Property(property="image", type="string", format="binary", description="File gambar yang akan diunggah."),
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Gambar berhasil dibuat",
     * @OA\JsonContent(ref="#/components/schemas/Image")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validasi gagal"
     * )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('images', 'public');

        $product = Product::create([
            'title' => $validated['title'],
            'file_path' => $path,
        ]);

        return response()->json($product, 201);
    }

    /**
     * @OA\Get(
     * path="/api/product/{id}",
     * operationId="getProductById",
     * tags={"Products"},
     * summary="Mengambil data satu gambar",
     * security={{"ApiKeyAuth":{}}},
     * description="Mengembalikan detail data gambar berdasarkan ID.",
     * @OA\Parameter(
     * name="id",
     * description="ID Gambar",
     * required=true,
     * in="path",
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Operasi berhasil",
     * @OA\JsonContent(ref="#/components/schemas/Image")
     * ),
     * @OA\Response(
     * response=404,
     * description="Data tidak ditemukan"
     * )
     * )
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * @OA\Delete(
     * path="/api/product/{id}",
     * operationId="deleteProduct",
     * tags={"Products"},
     * summary="Menghapus gambar",
     * security={{"ApiKeyAuth":{}}},
     * description="Menghapus file gambar dari storage dan data dari database berdasarkan ID.",
     * @OA\Parameter(
     * name="id",
     * description="ID Gambar yang akan dihapus",
     * required=true,
     * in="path",
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Gambar berhasil dihapus",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Image deleted successfully.")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Data tidak ditemukan"
     * )
     * )
     */
    public function destroy(Product $product)
    {
        // Hapus file fisik dari storage
        Storage::disk('public')->delete($product->file_path);

        // Hapus record dari database
        $product->delete();

        return response()->json(['message' => 'Image deleted successfully.'], 200);
    }
}
