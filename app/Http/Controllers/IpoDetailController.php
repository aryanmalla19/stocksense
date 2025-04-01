<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IpoDetail;

class  IpoDetailController extends Controller{
    
    public function fetch($id){
        $ipoDetail = IpoDetail::find($id);

        if(!$ipoDetail){
            return response()->json(['error'=>'IPO Detail not found'],404);
        }
        return response()->json($ipoDetail);
    }
    public function store(Request $request){
        $attributes = $request->validate([
            [
                'stock_id' => 'required|integer',
                'issue_price' => 'required|integer|min:100',
                'total_shares' => 'required|integer|min:1000',
                'open_date' => 'required|date',
                'close_date'=> 'required|date|after:open_date',
                'listing_date' => 'required|date|after:close_date',
                'ipo_status' => 'required|string|in:open,close,pending',
        ]
        ]);

        $ipoDetail = IpoDetail::create($attributes);
        return response()->json($ipoDetail,201);
    }
}