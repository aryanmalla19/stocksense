<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use App\Models\IpoApplication;

class  IpoApplicationController extends Controller{
    
    public function store(Request $request){

        $attributes = $request->validate([
            [

                'user_id' => 'required|integer',
                'ipo_id' => 'required|integer',
                'applied_shares' => 'required|integer|min:10',
                'status' => 'required|string|in:open,close,pending',
                'applied_date' => 'required|date',
                'allotted_shares'=>  'nullable|integer',
        ]
        ]);

        $ipoApplication = IpoApplication::create($attributes);
        return response()->json([$ipoApplication,201]);

    }


}