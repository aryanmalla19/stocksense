<?php

namespace App\Http\Controllers;

use App\Models\IpoApplication;
use App\Models\IpoDetail;
use App\Notifications\IpoAlloted;

class IpoAllotmentController extends Controller
{
    public function ipoAllotment(string $ipo_id)
    {
        $ipo_details = IpoDetail::findOrFail($ipo_id);
        $totalUserAllot = intval($ipo_details->total_shares / 10); 

        $allApplications = IpoApplication::where('ipo_id', $ipo_id)->get();

        if ($allApplications->count() <= $totalUserAllot) {
            foreach ($allApplications as $application) {
                $application->update(['status' => 'allotted', 'alloted_shares' => 10]);
                if ($application->user) {
                    $application->user->notify(new IpoAlloted($ipo_details));
                }
            }
            return response()->json(['message' => 'All applications allotted']);
        }

        $selectedApplications = $allApplications->random($totalUserAllot);

        $selectedIds = $selectedApplications->pluck('id')->toArray();

        IpoApplication::whereIn('id', $selectedIds)
            ->update(['status' => 'allotted', 'alloted_shares' => 10]);

        foreach ($selectedApplications as $application) {
            if ($application->user) {
                $application->user->notify(new IpoAlloted($ipo_details));
            }
        }

        IpoApplication::where('ipo_id', $ipo_id)
            ->whereNotIn('id', $selectedIds)
            ->update(['status' => 'not_allotted']);

        return response()->json(['message' => 'IPO shares distributed successfully.']);
    }

}
