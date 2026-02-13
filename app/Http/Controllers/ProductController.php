<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;

class ProductController extends Controller
{
    //
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'                  => 'required|string|max:255',
                'description'           => 'required|string',
                'brands'                => 'required|array|min:1',
                'brands.*.name'         => 'required|string|max:255',
                'brands.*.detail'       => 'nullable|string',
                'brands.*.price'        => 'required|numeric|min:0',
                'brands.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            ]);
            $user = $request->user();
            $seller = Seller::where('user_id', $user->id)->first();
             if (! $seller) {
                return response()->json([
                    'message' => 'Seller profile not found.',
                ], 404);
            }

            DB::beginTransaction();

            $product = Product::create([
                'seller_id'   => $seller->id,
                'name'        => $validated['name'],
                'description' => $validated['description'],
            ]);

            foreach ($validated['brands']  as $index => $brandData) {
                // for images to store 
                  $imagePath = null;
                if ($request->hasFile("brands.$index.image")) {
                    /** @var UploadedFile $file */
                    $file = $request->file("brands.$index.image");
                    $imagePath = $file->store('brand-images', 'public'); // storage/app/public/brand-images
                }
                $product->brands()->create([
                    'name'   => $brandData['name'],
                    'detail' => $brandData['detail'] ?? null,
                    'image'  => $imagePath, 
                    'price'  => $brandData['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully',
                'data'    => $product->load('brands'),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            //dd($e);
            DB::rollBack();

            return response()->json([
                'message' => 'Server error',
            ], 500);
        }
    }

    //  Product listing (only this seller, with pagination)
    public function index(Request $request)
    {
        $user = $request->user();

        $seller = Seller::where('user_id', $user->id)->first();
        if (! $seller) {
            return response()->json([
                'message' => 'Seller profile not found.',
            ], 404);
        }

        $perPage = (int) $request->get('per_page', 10);

        $products = Product::with('brands')
            ->where('seller_id', $seller->id) // use seller table id
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($products, 200);
    }

    //  View PDF for a product 
    public function pdf(Product $product, Request $request)
    {
        // ownership check 
        $user = $request->user();
        $seller = Seller::where('user_id', $user->id)->first();

        if (! $seller || $product->seller_id !== $seller->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $product->load('brands');
        $totalPrice = $product->brands->sum('price');

        $pdf = Pdf::loadView('pdf.product', [
            'product'     => $product,
            'total_price' => $totalPrice,
        ]);

        return $pdf->stream('product-'.$product->id.'.pdf');
    }

    // 4. Delete product
    public function destroy(Product $product, Request $request)
    {
        if ($product->seller_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server error',
            ], 500);
        }
    }

}
