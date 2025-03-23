<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Models\Items;


class ItemsController extends Controller
{
    //
    public function index()
    {
        try {

            $Items = Items::orderBy('id', 'desc')
                ->get();
            return response()->json(['success' => true, 'items' =>  $Items]);
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

            $item = Items::find($id);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }
            return response()->json(['success' => true, 'items' =>  $item]);
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

            $validationInput = $request->validate(
                [
                'po_no' => 'required|string|max:50',
                'brand_name' => 'required|string|max:100',
                'generic_name' => 'required|string|max:100',
                'dosage_form' => 'nullable|string|max:50',
                'dosage' => 'required|string|max:50',
                'category' => 'nullable|string|max:50',
                'unit' => 'required|string|max:50',
                'quantity' => 'required|numeric|min:1',
                'expiration_date' => 'required|date|after:today',
                'user_id' => 'required|exists:tbl_system_users,id',
                ]
            );

            $Items = Items::create($validationInput);
            return response()->json([
                'success' => true,
                'items' =>  $Items
            ]);
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

    public function update(Request $request, $id) {
        try {
            $item = Items::find($id);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'item not found'], 404);
            }

            $validationInput = $request->validate(
                [
                    'po_no' => 'required|string|max:50',
                    'brand_name' => 'required|string|max:100',
                    'generic_name' => 'required|string|max:100',
                    'dosage_form' => 'nullable|string|max:50',
                    'dosage' => 'required|string|max:50',
                    'category' => 'nullable|string|max:50',
                    'unit' => 'required|string|max:50',
                    'quantity' => 'required|numeric|min:1',
                    'expiration_date' => 'required|date|after:today',
                    'user_id' => 'required|exists:tbl_system_users,id',
                ]
            );

            $item->update($validationInput);
            return response()->json([
                'success' => true,
                'items' =>  $item
            ]);
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


    public function destroyItemsByPO($po_number) {
        try {
            $item = Items::where('po_no',$po_number)
                            ->get();
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'item not found'], 404);
            }
            // $item->delete();
            Items::where('po_no',$po_number)->delete();

            return response()->json([
                'success' => true,
                'items' =>  $item
            ],200);
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

    public function destroy($id) {
        try {
            $item = Items::find($id);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'item not found'], 404);
            }
            $item->delete();
            return response()->json([
                'success' => true,
                'items' =>  $item
            ],200);
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

    public function getExpiringStock()
    {
        return Items::where('expiration_date', '<', now()->addDays(30))
                    ->get();
    }

 
}
