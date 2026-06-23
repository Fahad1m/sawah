<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;

class BookingController extends Controller
{
public function store(\Illuminate\Http\Request $r){
    if (!$r->user()) {
        return response()->json(['error' => 'يجب تسجيل الدخول لإتمام الحجز'], 401);
    }

    $data = $r->validate([
        'trip_id'      => 'required|exists:trips,id',
        'start_date'   => 'required|date',
        'people_count' => 'required|integer|min:1',
    ]);

    $trip = \App\Models\Trip::findOrFail($data['trip_id']);
    $booking = \App\Models\Booking::create([
        'trip_id'       => $trip->id,
        'user_id'       => $r->user()->id,
        'customer_name' => $r->user()->name,
        'customer_email'=> $r->user()->email,
        'start_date'    => $data['start_date'],
        'people_count'  => $data['people_count'],
        'amount'        => ($trip->price ?? 0) * (int)$data['people_count'],
        'status'        => 'pending',
    ]);

    return response()->json($booking->load('trip'), 201);
}


public function mine()
{
    $userId = auth()->id();

    $bookings = \App\Models\Booking::query()
        ->with(['trip:id,destination,price,image'])
        ->where('user_id', $userId)
        ->latest()
        ->get(['id','trip_id','status','start_date','people_count']); 

    $normalized = $bookings->map(function ($b) {
        $startDate = $b->start_date ?? $b->date; 
        return [
            'id'         => $b->id,
            'status'     => strtolower($b->status ?? 'pending'),
            'start_date' => $startDate,
            'people'     => (int)($b->people ?? 1),
            'trip'       => [
                'destination' => $b->trip->destination ?? '—',
                'price'       => (float)($b->trip->price ?? 0),
                'image'       => $b->trip->image ?? null,
            ],
        ];
    });

    return response()->json(['data' => $normalized], 200, ['Content-Type' => 'application/json']);
}

  public function index(){
    return response()->json(
      Booking::with('trip')->latest()->paginate(20),
      200, [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
    );
  }

  public function confirm($id){ Booking::findOrFail($id)->update(['status'=>'confirmed']); return response()->json(['ok'=>true]); }
  public function cancel($id){  Booking::findOrFail($id)->update(['status'=>'cancelled']); return response()->json(['ok'=>true]); }
  public function destroy($id){ Booking::whereKey($id)->delete(); return response()->json(['ok'=>true]); }
}
