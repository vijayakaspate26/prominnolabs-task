<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminSellerController extends Controller
{
    //
      public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'mobile'   => 'required|string|max:20',
                'country'  => 'required|string|max:100',
                'state'    => 'required|string|max:100',
                'skills'   => 'required|array|min:1',
                'skills.*' => 'string|max:100',
                'password' => 'required|string|min:8',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'seller',
            ]);

            $seller = Seller::create([
                'user_id' => $user->id,
                'mobile'  => $validated['mobile'],
                'country' => $validated['country'],
                'state'   => $validated['state'],
                'skills'  => json_encode($validated['skills']),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Seller created successfully',
                'data'    => [
                    'user'   => $user,
                    'seller' => $seller,
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
             dd($e);
            return response()->json([
                'message' => 'Server error',
            ], 500);
        }
    }

    // ========================
    // List Sellers with pagination
    // ========================
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);

        $sellers = Seller::with('user')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($sellers, 200);
    }

}
