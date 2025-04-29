<?php

namespace App\Http\Controllers;

use App\Http\Resources\IpoApplicationResource;
use App\Http\Resources\UserResource;
use App\Models\IpoDetail;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        $user = User::paginate(15);

        return $user;
    }

    public function user(Request $request, string $id)
    {
        $user = User::find($id);
        $user->load(['portfolio.holdings.stock', 'transactions', 'portfolio.holdings']);

        return new UserResource($user);
    }

    public function ipoApplications(string $id)
    {
        $ipoDetails = IpoDetail::find($id);
        $ipoApplications = $ipoDetails->applications();

        if (request('status')) {
            $ipoApplications->whereStatus(\request('status'));
        }

        $ipoApplications = $ipoApplications->paginate(10);

        return IpoApplicationResource::collection($ipoApplications);
    }
}
