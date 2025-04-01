<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Daily_transactions as Transactions;
use App\Models\items;
use Illuminate\Support\Facades\DB;


class DailyTransactionsController extends Controller
{
    //

    public function newTransactionID($id)
    {
        $dateNow = now()->format('Ymd');  // Get date as YYYYMMDD

        // Find the last transaction for this customer on the same date
        $lastTransaction = Transactions::where('customer_id', $id)
            ->whereDate('created_at', now()->toDateString()) // Ensures it's from the same date
            ->max('transaction_id');

        // Extract the last numeric part and increment
        if ($lastTransaction && preg_match('/\d{8}-\d+-\d+/', $lastTransaction)) {
            $parts = explode('-', $lastTransaction);
            $nextTransactionNumber = isset($parts[2]) ? (intval($parts[2]) + 1) : 1;
        } else {
            $nextTransactionNumber = 1; // Start from 1 if no transactions exist for today
        }

        // Ensure numbering is 6-digit padded (e.g., 000001)
        $transactionNumber = str_pad($nextTransactionNumber, 6, '0', STR_PAD_LEFT);

        return $dateNow . '-' . $id . '-' . $transactionNumber;
    }



    public function index()
    {
        try {

            $transactions = Transactions::orderBy('id', 'desc')
                ->get();
            return response()->json(['success' => true, 'transactions' =>  $transactions]);
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

            $transactions = Transactions::where('id',$id)
                ->get();
            return response()->json(['success' => true, 'transactions' =>  $transactions]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => 'Record not found '. $e,
            ], 404);
        }catch (ValidationException $ve) {
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


    public function showLatestOrder($transactionID)
    {
        try {

             // $data = DB::table('tbl_items')
        //     ->join('tbl_daily_inventory', 'tbl_items.id', '=', 'tbl_daily_inventory.stock_id') // Joining on the common column
        //     ->select(
        //         'tbl_items.id as item_id',
        //         'tbl_items.po_no',
        //         'tbl_items.brand_name',
        //         'tbl_items.generic_name',
        //         'tbl_items.dosage',
        //         'tbl_items.dosage_form',
        //         'tbl_items.unit',
        //         'tbl_items.quantity',
        //         'tbl_daily_inventory.Closing_quantity',
        //         'tbl_items.expiration_date',
        //     ) // Selecting specific columns
        //     ->get();

        $transactions = Transactions::where('transaction_id', $transactionID)
                    ->join('tbl_items', 'tbl_daily_transactions.item_id', '=', 'tbl_items.id')
                    ->select(
                        'tbl_daily_transactions.quantity',
                        'tbl_daily_transactions.customer_id',
                        'tbl_daily_transactions.id as table_id_transactions',
                        'tbl_items.brand_name',
                        'tbl_items.generic_name',
                        'tbl_items.dosage',
                        'tbl_items.dosage_form',
                        'tbl_items.unit',
                        'tbl_items.id as item_id'
                        )
                    ->get();
            return response()->json(['success' => true, 'transactions' =>  $transactions]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => 'Record not found '. $e,
            ], 404);
        }catch (ValidationException $ve) {
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
                    'item_id' => 'required|exists:tbl_items,id',
                    'transaction_id' => 'required|string',
                    'customer_id' => 'required|exists:tbl_customers,id',
                    'quantity' => 'required|numeric|min:1',
                    'transaction_date' => 'required|date',
                    'user_id' => 'required|exists:tbl_system_users,id'
                ]
            );

            $transactions = Transactions::create($validationInput);
            return response()->json([
                'success' => true,
                'customers' =>  $transactions
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
            $transactions = Transactions::find($id);
            if (!$transactions) {
                return response()->json(['success' => false, 'message' => 'transaction not found'], 404);
            }


            $validationInput = $request->validate(
                [
                    'item_id' => 'required|exists:tbl_items,id',
                    'transaction_id' => 'required|string',
                    'customer_id' => 'required|exists:tbl_customers,id',
                    'quantity' => 'required|numeric|min:1',
                    'transaction_date' => 'required|date',
                    'user_id' => 'required|exists:tbl_system_users,id'
                ]
            );

            $transactions->update($validationInput);
            return response()->json([
                'success' => true,
                'customers' =>  $transactions
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

    public function destroy($id) {
        try {
            $transactions = Transactions::where('id', $id)->firstOrFail();
            $transactions->delete();
            return response()->json([
                'success' => true,
                'message' => 'transaction deleted.',
            ],200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => 'Record not found : ' . $e,
            ], 404);
        }catch (ValidationException $ve) {
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
