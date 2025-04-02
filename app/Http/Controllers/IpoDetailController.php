<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IpoDetail;

class  IpoDetailController extends Controller{
    
    public function index(){
        $allIpoDetail = IpoDetail::all();
        return response()->json($$allIpoDetail);
    }
    public function show($id){
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

    public function update(Request $request){
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

        $ipoDetail = IpoDetail::find($request->id);
        $ipoDetail->update($attributes);
        return response()->json(['message' => 'successfully updated sector with ID']);

    }

    public function destroy($id){
        $ipoDetail = ipoDetail::find($id);
        if(empty($ipoDetail)){
            return response()->json(['message'=> 'could not find sector with ID'],404);
        }

        $ipoDetail->delete();

        return response()->json([
            'message' => 'Successfully deleted sector with ID'
        ]);
    }
}