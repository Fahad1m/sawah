<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Recommendation;

class RecommendationController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $today = now('Asia/Riyadh')->toDateString();

        $rateableBookings = Booking::query()
            ->with(['trip:id,destination'])            
            ->where('user_id', $userId)
            ->whereDate('start_date', '<', $today)
            ->whereIn('status', ['confirmed', 'completed'])
            ->get(['id','trip_id','start_date','status']);

        $alreadyRatedTripIds = Recommendation::where('user_id', $userId)->pluck('trip_id')->all();
        $rateableTrips = $rateableBookings
            ->reject(fn($b) => in_array($b->trip_id, $alreadyRatedTripIds))
            ->map(fn($b) => [
                'booking_id' => $b->id,
                'trip_id'    => $b->trip_id,
                'label'      => $b->trip?->destination . ' — ' . $b->start_date,
            ])
            ->values();

        $allRecs = Recommendation::with(['user:id,name', 'trip:id,destination'])
            ->latest()->get();

        return view('recommendations', [
            'rateableTrips' => $rateableTrips, 
            'recommendations' => $allRecs,
        ]);
    }

    public function store(Request $request)
    {
        $userId = auth()->id();
        $today  = now('Asia/Riyadh')->toDateString();

        $data = $request->validate([
            'trip_id' => ['required','integer','exists:trips,id'],
            'rating'  => ['required','integer', Rule::in([1,2,3,4,5])],
            'text'    => ['required','string','min:5','max:2000'],
        ]);

        $hasEligibleBooking = Booking::where('user_id', $userId)
            ->where('trip_id', $data['trip_id'])
            ->whereDate('start_date', '<', $today)
            ->whereIn('status', ['confirmed', 'completed'])
            ->exists();

        if (!$hasEligibleBooking) {
            return back()->withErrors(['trip_id' => 'لا توجد رحلة مكتملة تخصك لهذه الوجهة.']);
        }

        $alreadyRated = Recommendation::where('user_id', $userId)
            ->where('trip_id', $data['trip_id'])
            ->exists();

        if ($alreadyRated) {
            return back()->withErrors(['trip_id' => 'قيّمت هذه الرحلة مسبقًا.']);
        }

        Recommendation::create([
            'user_id' => $userId,
            'trip_id' => $data['trip_id'],
            'rating'  => $data['rating'],
            'text'    => $data['text'],
        ]);

        return back()->with('ok', true);
    }
}
