<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    
    /**
     * Menampilkan daftar semua produk.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * Menambahkan produk baru.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string',  // Validasi untuk gambar base64
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Mengonversi dan menyimpan gambar base64 jika ada
        $imagePath = null;
        if ($request->has('image')) {
            $imageData = $request->image;
            $image = str_replace('data:image/png;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'product_' . time() . '.png';

            // Menyimpan gambar dalam disk public
            Storage::disk('public')->put($imageName, base64_decode($image));
            $imagePath = $imageName;
        }

        // Menyimpan data produk
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,  // Menyimpan nama gambar
        ]);

        return response()->json([
            'message' => 'Product successfully created',
            'data' => $product
        ], 201);
    }

    /**
     * Menampilkan produk tertentu.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    /**
     * Mengupdate produk.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string',  // Validasi untuk gambar base64
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Menemukan produk berdasarkan ID
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Mengonversi dan menyimpan gambar base64 jika ada
        $imagePath = $product->image;  // Menggunakan gambar lama jika tidak ada gambar baru
        if ($request->has('image')) {
            // Menghapus gambar lama jika ada
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $imageData = $request->image;
            $image = str_replace('data:image/png;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'product_' . time() . '.png';

            // Menyimpan gambar baru
            Storage::disk('public')->put($imageName, base64_decode($image));
            $imagePath = $imageName;
        }

        // Mengupdate data produk
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,  // Menyimpan nama gambar
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Menghapus produk.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Menghapus gambar jika ada
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        // Menghapus produk
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    // Pencarian produk berdasarkan nama atau deskripsi
    public function search(Request $request)
    {
        $query = $request->query('query');  // Dapatkan parameter query dari URL

        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        // Mencari produk berdasarkan nama atau deskripsi
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found matching your search criteria',
                'products' => [],
            ], 200);  // Jika tidak ada produk yang ditemukan
        }

        return response()->json([
            'message' => 'Search results found',
            'products' => $products,
        ], 200);  // Jika produk ditemukan
    }
}
