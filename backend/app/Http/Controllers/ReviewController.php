<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // List all reviews (optionally filter by place)
    public function index(Request $request)
    {
        $query = Review::query();

        if ($request->has('place_id')) {
            $query->where('place_id', $request->place_id);
        }

        $reviews = $query->with(['user', 'place'])->get();

        return response()->json($reviews);
    }

    // Store a new review
    public function store(Request $request)
    {
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'rating'   => 'required|string|max:5', // assuming rating stored as string like '4' or '5'
            'comment'  => 'required|string',
        ]);

        // Assuming user must be authenticated
        $review = Review::create([
            'user_id'  => Auth::id(),
            'place_id' => $validated['place_id'],
            'rating'   => $validated['rating'],
            'comment'  => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Review created successfully',
            'review'  => $review,
        ], 201);
    }

    // Show a single review
    public function show($id)
    {
        $review = Review::with(['user', 'place'])->findOrFail($id);

        return response()->json($review);
    }

    // Update a review (only owner can update)
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Check ownership
        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rating'  => 'sometimes|string|max:5',
            'comment' => 'sometimes|string',
        ]);

        $review->update($validated);

        return response()->json([
            'message' => 'Review updated successfully',
            'review'  => $review,
        ]);
    }

    // Delete a review (only owner can delete)
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Check ownership
        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
