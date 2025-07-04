<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Models\Medicinelibrary;

class MedicinelibraryController extends Controller
{
    //
    public function index()
    {
        try {

            $Items = Medicinelibrary::orderBy('id', 'desc')
                ->get();
            return response()->json(
                [
                    'success' => true,
                    'items' =>  $Items
                ],
                200
            );
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

            $item = Medicinelibrary::where('id', $id)->get();
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



    public function batch_Store(Request $request)
    {
        try {

            $validated = $request->validate([
                'medicines' => 'required|array',
                'medicines.*.brand_name' => 'required|string',
                'medicines.*.generic_name' => 'required|string',
                'medicines.*.dosage_form' => 'required|string',
                'medicines.*.dosage' => 'required|string',
                'medicines.*.category' => 'nullable|string',
                'medicines.*.user_id' => 'required|integer',
            ]);

            $inserted = [];
            $skipped = [];

            foreach ($validated['medicines'] as $medicine) {
                $exists = Medicinelibrary::where('brand_name', $medicine['brand_name'])
                    ->where('generic_name', $medicine['generic_name'])
                    ->where('dosage_form', $medicine['dosage_form'])
                    ->where('dosage', $medicine['dosage'])
                    ->exists();

                if ($exists) {
                    $skipped[] = $medicine;
                    continue;
                }

                $inserted[] = Medicinelibrary::create($medicine);
            }




            // $inserted = Medicinelibrary::insert($validated['medicines']);

            return response()->json([
                'success' => true,
                'inserted_count' => count($inserted),
                'skipped_count' => count($skipped),
                'message' => 'Insert process completed.',
                'inserted' => $inserted,
                'skipped' => $skipped,
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

    public function store(Request $request)
    {

        try {

            $validationInput = $request->validate(
                [
                    'brand_name' => 'required|string|max:100',
                    'generic_name' => 'required|string|max:100',
                    'dosage_form' => 'nullable|string|max:50',
                    'dosage' => 'required|string|max:50',
                    'category' => 'nullable|string|max:50',
                    'user_id' => 'required|exists:tbl_system_users,id',
                ]
            );

            $Items = Medicinelibrary::create($validationInput);
            return response()->json([
                'success' => true,
                'item' =>  $Items,
                'message' => 'Item registration Successful'
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

    public function update(Request $request, $id)
    {
        try {
            $item = Medicinelibrary::where('id', $id)->first();
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'item not found'], 404);
            }

            $validationInput = $request->validate(
                [
                    'brand_name' => 'required|string|max:100',
                    'generic_name' => 'required|string|max:100',
                    'dosage_form' => 'nullable|string|max:50',
                    'dosage' => 'required|string|max:50',
                    'category' => 'nullable|string|max:50',
                    'user_id' => 'required|exists:tbl_system_users,id',
                ]
            );
            $item->update($validationInput);

            return response()->json([
                'success' => true,
                'item' =>  $item,
                'message' => 'Item updating Successful'
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

    public function destroy($id)
    {
        try {
            Medicinelibrary::where('id', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'item deleted successfully'
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
}
