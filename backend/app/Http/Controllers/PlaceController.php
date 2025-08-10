<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
class PlaceController extends Controller
{
     public static function middleware(){
        return[
            new Middleware('auth:sanctum',except:['index','show'])
        ];
     }
    public function index()
    {
        $places = Place::latest()->get();
        return response()->json($places);
    }


public function store(Request $request)
{
    Gate::authorize('placecreate', Place::class);

    try {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'address'     => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'phone_number'=> 'nullable|string',
            'website'     => 'nullable|string',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('places', 'public');
                $imagePaths[] = $path;
            }
        }

        $place = Place::create([
            'name'        => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'address'     => $request->address,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'phone_number'=> $request->phone_number,
            'website'     => $request->website,
            'images'      => json_encode($imagePaths),
            'owner_id'    => Auth::id(),  // Add owner_id here
        ]);

        return response()->json([
            'message' => 'Place created successfully',
            'place'   => $place
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors'  => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error creating place: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred while creating the place',
            'error' => $e->getMessage(),  // For debugging only
        ], 500);
    }
}

    public function show($id)
    {
        $place = Place::findOrFail($id);
        return response()->json($place);
    }

public function update(Request $request, Place $place)
{
    try {
        Gate::authorize('placemodify', $place);

        $updatedData = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'address'     => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'phone_number'=> 'nullable|string|max:20',
            'website'     => 'nullable|string|max:255',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

         $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('places', 'public');
                $imagePaths[] = $path;
            }
        }

        $place->update($updatedData);

        return response()->json([
            'message' => 'Place updated successfully',
            'place' => $place,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        \Log::error('Error updating place: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred while updating the place',
            'error' => $e->getMessage(), // For debugging
        ], 500);
    }
}



    public function destroy(Place $place)
    {
        Gate::authorize('placemodify',$place);

        $place->delete();

        return response()->json(['message' => 'Place deleted successfully']);
    }
}
