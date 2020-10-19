<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\Voucher;
use App\Traits\OfferTransformer;
use App\Traits\Transformer;
use Illuminate\Http\Request;
use DB;

class CustomerOfferController extends Controller
{
    /**
     * generate voucher against customer
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateVoucher(Request $request)
    {
        DB::beginTransaction();
        try {
            $date_time = date('Y-m-d H:i:s');
            if (!empty($request->offer)) {
                if (!empty($request->offer['id'])) {
                    $offer_id = $request->offer['id'];
                } else {
                    $offer = Offer::create([
                        'name' => $request->offer['name'],
                        'discount' => $request->offer['discount'],
                    ]);
                    $offer_id = $offer->id;
                }
            }

            if (!empty($request->customer)) {
                if (!empty($request->customer['id'])) {
                    $customer_id = $request->customer['id'];
                } else {
                    $customer = Customer::whereEmail($request->customer['email'])->first();
                    if (!empty($customer)) {
                        return response()->json(['status' => 400, 'message' => 'Customer email already exist']);
                    } else {
                        $customer = Customer::create([
                            'name' => $request->customer['name'],
                            'email' => $request->customer['email'],
                        ]);
                    }
                    $customer_id = $customer->id;
                }
            }
            $voucher_data = [
                'expire_date' => date('Y-m-d', strtotime('+3 days', strtotime(date('Y-m-d')))),
                'is_active' => 'true',
                'offer_id' => $offer_id,
                'customer_id' => $customer_id,
                'code' => mb_substr(rand(100000000, strtotime(date('Y-m-d H:i:s'))), 0, 8)
            ];
            $voucher = Voucher::create($voucher_data);
            DB::commit();
            return response()->json(['data' => $voucher], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * get voucher details
     * @return \Illuminate\Http\JsonResponse
     */
    public function voucherDetails()
    {
        $rules = [
            'email' => 'required|exists:customers,email',
            'voucher_code' => 'required|exists:vouchers,code'
        ];
        request()->validate($rules, []);
        try {
            DB::beginTransaction();
            $voucherData = Voucher::where('is_active', 'true')->where('expire_date', '>', date('Y-m-d'))->whereHas('customer', function ($query) {
                $query->whereEmail(request('email'));
            })->whereHas('offer', function ($query) {
                $query->whereCode(request('voucher_code'));
            })->with(['customer' => function ($query) {
                $query->select('id', 'name');
            }, 'offer' => function ($query) {
                $query->select('id', 'name', 'discount');
            }])->first();
            if (!empty($voucherData)) {
                $voucher = [
                    'offer_name' => $voucherData->offer->name,
                    'discount' => $voucherData->offer->discount . "%",
                    'customer_name' => $voucherData->customer->name,
                    'date_code_used' => $voucherData->date_code_used,
                    'is_active' => $voucherData->is_active
                ];
                Voucher::whereId($voucherData->id)->update(['date_code_used' => date('Y-m-d'), 'is_active' => 'false']);
                DB::commit();
                return response()->json(['data' => $voucher], 200);
            } else {
                return response()->json(['message' => 'Voucher is invalid'], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * get customer vouchers.
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerVouchers()
    {
        $rules = [
            'email' => 'required|exists:customers,email',
        ];
        request()->validate($rules, []);
        try {
            $limit = request('limit') ? request('limit') : 10;
            $vouchers = Voucher::where('is_active', 'true')->where('expire_date', '>', date('Y-m-d'))->whereNull('date_code_used')->whereHas('customer', function ($query) {
                $query->whereEmail(request('email'));
            })->with(['offer' => function ($query) {
                $query->select('id', 'name', 'discount');
            }])->paginate($limit);
            $meta = Transformer::transformCollection($vouchers);
            $transformedOffers = OfferTransformer::transformVouchers($vouchers);
            return response()->json(['data' => $transformedOffers, "meta" => $meta], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
