<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{

    public function index()
    {
        return view("index");
    }

    

public function apiIndex(Request $r)
{
    $q = Trip::query();

    if ($r->filled('q')) {
        $q->where(function($x) use ($r) {
            $x->where('destination', 'like', '%'.$r->q.'%')
              ->orWhere('description', 'like', '%'.$r->q.'%');
        });
    }
    if ($r->filled('destination')) $q->where('destination','like','%'.$r->destination.'%');
    if ($r->filled('min_price'))  $q->where('price','>=',(float)$r->min_price);
    if ($r->filled('max_price'))  $q->where('price','<=',(float)$r->max_price);
    if ($r->filled('days'))       $q->where('duration',(int)$r->days);

    $trips = $q->latest()->paginate(12);

    $payload = $trips->through(fn($t) => [
        'id'          => $t->id,
        'destination' => $t->destination,
        'description' => $t->description,
        'price'       => $t->price,
        'duration'    => $t->duration,
        'rating'      => $t->rating,
        'features'    => $t->features,
        'image'       => $t->image_url, 
    ]);

    return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}


    public function store(Request $request)
    {
        $trip = Trip::create($request->all());
        return response()->json($trip, 201);
    }


    public function show($id)
    {
        return response()->json(Trip::findOrFail($id));
    }


    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        $trip->update($request->all());
        return response()->json($trip);
    }


    public function destroy($id)
    {
        Trip::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
