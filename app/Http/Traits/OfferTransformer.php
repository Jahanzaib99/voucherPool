<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait OfferTransformer
{

    public static function transformOffers($collection)
    {
        $offers = [];
        foreach ($collection as $offer) {
            $temp = [
                "id" => $offer->id,
                "name" => (string)$offer->name,
                "discount" => (string)$offer->discount . "%"
            ];
            array_push($offers, $temp);
        }
        return $offers;
    }

    public static function transformOffer($offer)
    {
        $offer_temp = [
            "id" => $offer->id,
            "name" => (string)$offer->name,
            "discount" => (string)$offer->discount . "%"
        ];
        return $offer_temp;
    }

    public static function transformVouchers($vouchers)
    {
        $transformed_vouchers = [];
        foreach ($vouchers as $voucher) {
            $temp = [
                "id" => $voucher->id,
                "code" => $voucher->code,
                "offer_name" => (string)$voucher->offer->name,
                "discount" => (string)$voucher->offer->discount . "%"
            ];
            array_push($transformed_vouchers, $temp);
        }
        return $transformed_vouchers;
    }

}
