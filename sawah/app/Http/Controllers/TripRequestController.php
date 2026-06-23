<?php

namespace App\Http\Controllers;

use App\Models\TripRequest;
use Illuminate\Http\Request;

class TripRequestController extends Controller {
    public function index(Request $r){
        $q = TripRequest::query()->latest();
        if($r->filled('status')) $q->where('status',$r->status);
        return response()->json($q->paginate(20),200,[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function mine(Request $r){
        $r->validate(['user'=>'required|string']);
        $items = TripRequest::where('user_name',$r->user)->latest()->get();
        return response()->json($items,200,[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function store(Request $r){
        $data = $r->validate([
            'user_name'=>'required|string|max:100',
            'user_email'=>'nullable|email',
            'destination'=>'required|string|max:255',
            'date'=>'required|date',
            'people'=>'required|integer|min:1',
            'budget'=>'required|integer|min:0',
            'details'=>'nullable|string',
        ]);
        $data['status'] = 'pending';
        $req = TripRequest::create($data);
        return response()->json($req,201,[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function approve($id){ TripRequest::findOrFail($id)->update(['status'=>'approved']); return response()->json(['ok'=>true]); }
    public function reject($id){ TripRequest::findOrFail($id)->update(['status'=>'rejected']); return response()->json(['ok'=>true]); }
    public function destroy($id){ TripRequest::whereKey($id)->delete(); return response()->json(['ok'=>true]); }
}
