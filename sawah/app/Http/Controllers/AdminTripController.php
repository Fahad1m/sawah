<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\User;

class AdminTripController extends Controller
{

    public function index(Request $request)
    {
        if ($request->is('admin/trips')) {
            $trips = Trip::latest()->paginate(12);
            return view('admin.trips.trips', compact('trips'));
        }

        $stats = [
            'tripsCount'          => Trip::count(),
            'usersCount'          => User::count(),
            'pendingBookingsCount'=> Booking::where('status', 'pending')->count(),
            'pendingRequestsCount'=> BookingRequest::where('status','pending')->count(),
        ];

        $pendingBookings = Booking::query()
            ->with(['trip:id,destination,price', 'user:id,name'])
            ->where('status','pending')
            ->latest()
            ->get(['id','trip_id','user_id','start_date','people_count','amount','status']);

        $pendingRequests = BookingRequest::query()
            ->with(['booking.trip:id,destination', 'user:id,name'])
            ->where('status','pending')
            ->latest()
            ->get([
                'id','booking_id','user_id','type','reason_code','note',
                'new_start_date','new_people_count','status','created_at'
            ]);

        return view('admin.trips.index', compact('stats','pendingBookings','pendingRequests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'destination' => ['required','string','max:255'],
            'price'       => ['required','numeric','min:0'],
            'duration' =>    ['required','numeric','min:0'],
            'image'       => ['nullable','string'],
            'description' => ['nullable','string']
        ]);

        Trip::create([
            'destination' => $data['destination'],
            'price'       => $data['price'],
            'duration' => $data['duration']
            ,
            'image'       => $data['image'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.trips.manage')->with('ok', true);
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();
        return redirect()->route('admin.trips.manage')->with('ok', true);
    }

  public function approveBooking(Booking $booking)
{
    if ($booking->status !== 'pending') {
        return response()->json(['ok' => false, 'msg' => 'Already handled'], 422);
    }

    $booking->forceFill([
        'status'     => 'confirmed',
        'updated_at' => now(),
    ])->save();

    return response()->json(['ok' => true]);
}

public function rejectBooking(Request $request, Booking $booking)
{

    if ($booking->status !== 'pending') {
        return response()->json(['ok' => false, 'msg' => 'Already handled'], 422);
    }

    $booking->forceFill([
        'status'     => 'cancelled',
        'updated_at' => now(),
    ])->save();

    return response()->json(['ok' => true]);
}

    public function approveBookingRequest(BookingRequest $bookingRequest)
    {
        if ($bookingRequest->status !== 'pending') {
            return response()->json(['ok'=>false,'msg'=>'Already handled'], 422);
        }

        DB::transaction(function () use ($bookingRequest) {
            $bookingRequest->forceFill([
                'status'      => 'approved',
                'admin_id'    => auth()->id(),
                'resolved_at' => now(),
                'admin_reason'=> null,
            ])->save();
            $booking = $bookingRequest->booking()->lockForUpdate()->first();
            if ($booking && $bookingRequest->type === 'cancel') {
                $booking->update(['status' => 'cancelled']);
            } elseif ($booking && $bookingRequest->type === 'modify') {
                $payload = [];
                if ($bookingRequest->new_start_date)  $payload['start_date']   = $bookingRequest->new_start_date;
                if ($bookingRequest->new_people_count) $payload['people_count'] = $bookingRequest->new_people_count;
                if (!empty($payload)) $booking->update($payload);
            }
        });

        return response()->json(['ok'=>true]);
    }

     public function rejectBookingRequest(Request $request, BookingRequest $bookingRequest)
    {
        if ($bookingRequest->status !== 'pending') {
            return response()->json(['ok'=>false,'msg'=>'Already handled'], 422);
        }

        $data = $request->validate([
            'admin_reason' => ['required','string','max:500'],
        ]);

        $bookingRequest->forceFill([
            'status'       => 'rejected',
            'admin_id'     => auth()->id(),
            'admin_reason' => $data['admin_reason'],
            'resolved_at'  => now(),
        ])->save();

        return response()->json(['ok'=>true]);
    }
}
