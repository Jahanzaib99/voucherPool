<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Traits\OfferTransformer;
use App\Traits\Transformer;
use Illuminate\Http\Request;
use DB;

class OffersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $limit = request('limit') ? request('limit') : 10;
            $offers = Offer::paginate($limit);
            $meta = Transformer::transformCollection($offers);
            $transformedOffers = OfferTransformer::transformOffers($offers);
            return response()->json(['data' => $transformedOffers, "meta" => $meta], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'discount' => 'required'
        ];
        $request->validate($rules, []);
        try {
            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'discount' => $request->discount,
            ];
            Offer::create($data);
            DB::commit();
            return response()->json(['message' => 'Offer added successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $offer = Offer::whereId($id)->first();
            if (!empty($offer)) {
                $transformed_Offer = OfferTransformer::transformOffer($offer);
                return response()->json(['data' => $transformed_Offer], 200);
            } else {
                return response()->json(['message' => 'Offer not found'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $rules = [
            'id' => 'nullable|exists:offers,id',
            'name' => 'required',
            'discount' => 'required'
        ];
        $request->validate($rules, []);
        try {
            DB::beginTransaction();
            $offer = Offer::whereId($request->id)->first();
            if (!empty($offer)) {
                $data = [
                    'name' => $request->name,
                    'discount' => $request->discount,
                ];
                Offer::whereId($request->id)->update($data);
                DB::commit();
                return response()->json(['message' => 'Offer updated successfully'], 200);
            } else {
                return response()->json(['message' => 'Offer not found'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $offer = Offer::whereId($id)->first();
            if (!empty($offer)) {
                Offer::whereId($id)->delete();
                return response()->json(['message' => 'Offer deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'Offer not found'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
