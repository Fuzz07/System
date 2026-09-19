<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class OfficerApiController extends Controller
{
    /**
     * Get active SSC officers roster.
     */
    public function index()
    {
        $officers = User::activeOfficers()->get()->map(function ($officer) {
            return [
                'id'         => $officer->id,
                'fullname'   => $officer->fullname,
                'position'   => $officer->position,
                'department' => $officer->department,
                'party'      => $officer->party,
                'photo_url'  => $officer->photo_url,
                'avatar'     => $officer->avatar,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $officers,
        ]);
    }
}
