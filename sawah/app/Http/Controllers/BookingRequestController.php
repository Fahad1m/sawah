<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingRequestController extends Controller
{

public function index()
{
    $requests = BookingRequest::where('user_id', auth()->id())
        ->with('trip')
        ->orderBy('created_at', 'desc')
        ->paginate(10); 

    return view('requests', compact('requests'));
}

    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);

         $data = $request->validate([
            'reason' => ['nullable','string','max:255'],
            'note'   => ['nullable','string','max:1000'],
        ]);

        $req = BookingRequest::create([
            'booking_id'     => $booking->id,
            'user_id'        => auth()->id(),
            'type'           => 'cancel',
            'status'         => 'pending',
            'reason_code'    => $data['reason'] ?? null, // <-- map
            'note'           => $data['note']   ?? null,
        ]);

        return response()->json(['ok'=>true, 'id'=>$req->id, 'status'=>$req->status], 201);
    }

     public function modify(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);

         $data = $request->validate([
            'new_date'   => ['required','date','date_format:Y-m-d'],
            'new_people' => ['required','integer','min:1'],
            'reason'     => ['nullable','string','max:255'],
            'note'       => ['nullable','string','max:1000'],
        ]);

        $req = BookingRequest::create([
            'booking_id'       => $booking->id,
            'user_id'          => auth()->id(),
            'type'             => 'modify',
            'status'           => 'pending',
            'reason_code'      => $data['reason']      ?? null, // <-- map
            'note'             => $data['note']        ?? null,
            'new_start_date'   => $data['new_date'],           // <-- map
            'new_people_count' => $data['new_people'],         // <-- map
        ]);

        return response()->json(['ok'=>true, 'id'=>$req->id, 'status'=>$req->status], 201);
    }

    public function adminIndex(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $q = BookingRequest::with(['booking', 'user'])->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->filled('type')) {
            $q->where('type', $request->string('type'));
        }

        return response()->json($q->paginate(20));
    }

 
    public function approve(Request $request, BookingRequest $bookingRequest)
    {
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }
        if ($bookingRequest->status !== 'pending') {
            return response()->json(['message' => 'تمت معالجة هذا الطلب مسبقاً.'], 422);
        }

         $booking = $bookingRequest->booking;

        if ($bookingRequest->type === 'modify') {
            if ($bookingRequest->new_start_date) {
                $booking->start_date = $bookingRequest->new_start_date;
            }
            if ($bookingRequest->new_people_count) {
                $booking->people_count = $bookingRequest->new_people_count;
            }
            $booking->save();
        }

        if ($bookingRequest->type === 'cancel') {
             if (property_exists($booking, 'status') || array_key_exists('status', $booking->getAttributes())) {
                $booking->status = 'cancelled';
                $booking->save();
            }
        }

        $bookingRequest->update([
            'status'       => 'approved',
            'admin_id'     => Auth::id(),
            'admin_reason' => $request->string('admin_reason') ?: null,
            'resolved_at'  => now(),
        ]);

        return response()->json($bookingRequest);
    }

 
    public function reject(Request $request, BookingRequest $bookingRequest)
    {
        if (!$this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }
        if ($bookingRequest->status !== 'pending') {
            return response()->json(['message' => 'تمت معالجة هذا الطلب مسبقاً.'], 422);
        }

        $data = $request->validate([
            'admin_reason' => ['required', 'string', 'max:2000'],
        ]);

        $bookingRequest->update([
            'status'       => 'rejected',
            'admin_id'     => Auth::id(),
            'admin_reason' => $data['admin_reason'],
            'resolved_at'  => now(),
        ]);

        return response()->json($bookingRequest);
    }

  
    private function isAdmin(): bool
    {
        $u = Auth::user();
        if (!$u) return false;

         if (isset($u->is_admin)) {
            return (bool) $u->is_admin;
        }
        if (isset($u->role)) {
            return $u->role === 'admin';
        }
        return false;
    }
}
