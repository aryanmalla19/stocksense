<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\Stock;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sectors = Sector::all();
        return response()->json([
            'message' => 'Successfully fetched all sectors data',
            'data' => $sectors
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
             'name' => 'required|in:banking,hydropower,life Insurance,non-life Insurance,health,manufacturing,hotel,trading,microfinance,finance,investment,others',
        ]);

        $sector = Sector::create($data);
        return response()->json([
            'message' => 'Successfully created sector',
            'data' => $sector
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sector = Sector::find($id);
        if (!$sector) {
            return response()->json([
                'message' => 'Could not find sector with ID ' . $id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched sector data',
            'data' => $sector
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sector = Sector::find($id);
        if(empty($sector)){
            return response()->json([
                'message' => 'Could not find sector with ID ' . $id,
            ], 404);
        }

        $data = $request->validate([
             'name' => 'required|in:banking,hydropower,life Insurance,non-life Insurance,health,manufacturing,hotel,trading,microfinance,finance,investment,others',
        ]);

        $sector->update($data);

        return response()->json([
            'message' => 'Successfully updated sector with ID '. $id,
            'data' => $sector
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sector = Sector::find($id);
        if(empty($sector)){
            return response()->json([
                'message' => 'Could not find sector with ID ' . $id,
            ], 404);
        }

        $sector->delete();
        return response()->json([
            'message' => 'Successfully deleted sector with ID ' . $id,
        ]);
    }
}
