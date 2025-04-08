<?php

namespace App\Http\Controllers;

use App\Models\IpoApplication;
use App\Models\IpoDetail;

class IpoAllotmentController extends Controller
{
    public function ipoAllotment(string $ipo_id)
    {
        $ipo_details = IpoDetail::findOrFail($ipo_id);
        $totalUserAllot = intval($ipo_details->total_shares / 10); 

        $allApplications = IpoApplication::where('ipo_id', $ipo_id)->get();

        if ($allApplications->count() <= $totalUserAllot) {
            foreach ($allApplications as $application) {
                $application->update(['status' => 'allotted']);
            }
            return response()->json(['message' => 'All applications allotted (less than or equal to required).']);
        }

        $selectedApplications = $allApplications->random($totalUserAllot);

        $selectedIds = $selectedApplications->pluck('id')->toArray();

        IpoApplication::whereIn('id', $selectedIds)
            ->update(['status' => 'allotted']);

        IpoApplication::where('ipo_id', $ipo_id)
            ->whereNotIn('id', $selectedIds)
            ->update(['status' => 'not_allotted']);

        return response()->json(['message' => 'IPO shares allotted successfully.']);
    }

}
