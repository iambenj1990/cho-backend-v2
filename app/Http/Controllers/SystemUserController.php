<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class SystemUserController extends Controller
{
    //
    public function index()
    {
        try {

            $System_users = User::orderBy('id', 'desc')
                ->get();
            return response()->json(['success' => true, 'users' =>  $System_users]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $System_users = User::where('id', $id)
                ->get();
            return response()->json(['success' => true, 'user' =>  $System_users]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validationInput = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'position' => 'required|string|max:100',
                'status' => 'required|string|max:100',
                'office' => 'required|string|max:100',
                'username' => 'required|string|max:100|unique:users,username',
                'password' => 'required|string|max:16',
            ]);

            // No need to hash password manually here
            $System_users = User::create($validationInput);

            return response()->json([
                'success' => true,
                'user' => $System_users
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        // try {
        //     $user = User::find($id);
        //     if (!$user) {
        //         return response()->json(['success' => false, 'message' => 'user not found'], 404);
        //     }

        //     $validationInput = $request->validate(
        //         [
        //             'first_name' => 'required|string|max:100',
        //             'last_name' => 'required|string|max:100',
        //             'middle_name' => 'nullable|string|max:100',
        //             'position' => 'required|string|max:100',
        //             'office' => 'required|string|max:100',
        //             'username' => 'required|string|max:100|unique:users,username',
        //             'password' => 'required|string|max:16',
        //         ]
        //     );

        //     $user->update($validationInput);
        //     return response()->json([
        //         'success' => true,
        //         'user' =>  $user
        //     ]);
        // } catch (ValidationException $ve) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Validation error',
        //         'errors' => $ve->errors()
        //     ], 422);
        //     //throw $th;
        // } catch (QueryException $qe) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Database error',
        //         'error' => $qe->getMessage()
        //     ], 500);
        //     //throw $th;
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'An unexpected error occurred',
        //         'error' => $th->getMessage()
        //     ], 500);
        // }

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            Log::info('Update User Request:', $request->all()); // Debug incoming data
            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'position' => 'required|string|max:100',
                'status' => 'required|string|max:100',
                'office' => 'required|string|max:100',
                'username' => 'required|string|max:100|unique:users,username,' . $user->id,
                'password' => 'nullable|string|min:8|max:16|confirmed',
            ]);

            // Only update password if it was provided
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }


            $user->update($validated);

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'user not found'], 404);
            }
            $user->delete();
            return response()->json([
                'success' => true,
                'user' =>  $user
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deactivateUser($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->status = 'Inactive';
            $user->save(); // use save() instead of update() here

            return response()->json([
                'success' => true,
                'user' => $user
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function activateUser($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->status = 'Active';
            $user->save(); // use save() instead of update() here

            return response()->json([
                'success' => true,
                'user' => $user
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    //-------------------------------------------------------------------LOGIN/LOGOUT--------------------------------------------------------------------------

    // public function login_User(Request $request){
    //     if(!Auth::attempt($request->only('username', 'password')))
    //     {
    //             return response()->json(['login_status'=> false, 'mssage'=> 'Login Failed: Invalid Credentials'],401);
    //     }

    //             $user =  Auth::user();
    //             $token = $user->createToken('CICTMO2025-CHO-INVENTORY-SYSTEM')->plainTextToken;
    //             $cookie = cookie('auth_token', $token, 60*24,null,null,true,true,false,'None');
    //             return response()->json([
    //                     'Login_Status' => true,
    //                     'message'=> 'login successfully',
    //                     'token' => $token

    //             ])->withCookie($cookie);
    // }
    public function login_User(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'username' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        // Check if user is active
        if ($user->status !== 'Active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact administrator.',
            ], 403);
        }

        // Revoke all existing tokens for this user (optional - for single session)
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('pharmacy-system')->plainTextToken;

        // Prepare user data for response (excluding sensitive fields)
        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name,
            'position' => $user->position,
            'office' => $user->office,
            'status' => $user->status,
            'username' => $user->username,
            'full_name' => trim($user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $userData,
                'token' => $token,
            ]
        ]);
    }

     public function logoutUser(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function getAuthenticatedUser(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }
}
