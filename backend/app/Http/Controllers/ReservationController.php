<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class ReservationController extends Controller
{
     public function index()
    {
        $reservations = Auth::user()->reservations()->with('place')->get();

        return response()->json($reservations);
    }

    // Store a new reservation
public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'place_id'         => 'required|exists:places,id',
            'reservation_date' => 'required|date|after:now',
            'number_of_people' => 'required|integer|min:1',
        ]);

        // Check if a reservation already exists for this user, place, and datetime
        $existing = Reservation::where('user_id', Auth::id())
            ->where('place_id', $validated['place_id'])
            ->where('reservation_date', $validated['reservation_date'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already made a reservation for this place at this date and time.',
            ], 409); // 409 Conflict
        }

        $reservation = Reservation::create([
            'user_id'         => Auth::id(),
            'place_id'        => $validated['place_id'],
            'reservation_date'=> $validated['reservation_date'],
            'number_of_people'=> $validated['number_of_people'],
            'status'          => 'pending',
        ]);

        return response()->json([
            'message' => 'Reservation created successfully',
            'reservation' => $reservation,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Validation failed
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        // Other unexpected errors
        \Log::error('Reservation creation error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'An error occurred while creating the reservation',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    // Show a specific reservation
    public function show($id)
    {
        $reservation = Reservation::with('place', 'user')->findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($reservation);
    }

    // Cancel or update reservation status
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,cancelled',
        ]);

        $reservation->update($validated);

        return response()->json([
            'message' => 'Reservation updated successfully',
            'reservation' => $reservation,
        ]);
    }

    // Delete reservation
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted successfully']);
    }
}