<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ip;

class IpController extends Controller
{
    
    public function index()
    {
        dd(request()->fingerprint());
        
        $ips = Ip::all();
        
        return response()->json([
            'ips' => $ips,
        ],200);
    }

    public function store(Request $request)
    {
        dd($request()->fingerprint());

        $ip = new Ip;
        
        $ip->ip = $request->ip;

        $ip->save();

        return response()->json([
            'resultado' => 'ok',
            ]
        );
    }
}
