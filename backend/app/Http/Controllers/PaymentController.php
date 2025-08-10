<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class PaymentController extends Controller
{
public function pay(Request $request)
{
    $chapaKey = env('CHAPA_SECRET_KEY');
    if (!$chapaKey) {
        return response()->json(['message' => 'Chapa API key missing.'], 500);
    }
$email = Auth::user()->email ?? ''; // fallback to empty string if not set

    // Validate input including items for restaurant category
    $request->validate([
        'reservation_id' => 'required|exists:reservations,id',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'required|string|max:50',
        'items' => 'nullable|array',
        'items.*.menu_id' => 'required_with:items|exists:menus,id',
        'items.*.quantity' => 'required_with:items|integer|min:1',
    ]);

    $reservation = Reservation::with('place.owner')->find($request->reservation_id);
    if (!$reservation) {
        return response()->json(['message' => 'Reservation not found.'], 404);
    }

    if (!in_array($reservation->status, ['pending', 'confirmed'])) {
        return response()->json(['message' => 'Reservation not eligible for payment.'], 422);
    }

    $place = $reservation->place;
    if (!$place || !$place->owner) {
        return response()->json(['message' => 'Place or place owner (payee) not found.'], 422);
    }

    $restaurantCategoryIds = [1, 2]; // Replace with your actual restaurant category IDs
    $totalAmount = 0;

    if (in_array($place->category_id, $restaurantCategoryIds)) {
        // Restaurant payment: must have menu items passed in request
        if (!$request->has('items') || count($request->items) === 0) {
            return response()->json(['message' => 'You must select menu items for a restaurant payment.'], 422);
        }

        foreach ($request->items as $item) {
            $menu = Menu::findOrFail($item['menu_id']);
            if ($menu->place_id != $place->id) {
                return response()->json(['message' => 'Invalid menu item selected.'], 422);
            }
            $totalAmount += $menu->price * $item['quantity'];
        }
    } else {
        // Non-restaurant payment: pay entrance fee
        if (!$place->entrance_fee) {
            return response()->json(['message' => 'Entrance fee not set for this place.'], 422);
        }
        $totalAmount = $place->entrance_fee;
    }

    $tx_ref = 'TX_' . uniqid();

    try {
        \DB::beginTransaction();

        // Update reservation status to 'payment_pending' or keep your flow
        $reservation->status = 'payment_pending';
        $reservation->save();

    $payment = Payment::create([
    'reservation_id' => $reservation->id,
    'payer_id' => Auth::id(),
    'payee_id' => $place->owner->id,
    'amount' => $totalAmount,
    'payment_method' => 'chapa',
    'status' => 'pending',
    'tx_ref' => $tx_ref,
    'first_name' => $request->first_name,
    'last_name' => $request->last_name,
    'email' => $email,
    'phone' => $request->phone,
]);


        if (!$payment) {
            \DB::rollBack();
            \Log::error('Failed to create payment record.', ['reservation_id' => $reservation->id]);
            return response()->json(['message' => 'Failed to create payment record.'], 500);
        }

        \DB::commit();

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Payment creation error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'message' => 'Failed to create payment.',
            'error' => $e->getMessage(),
        ], 500);
    }

    // Prepare valid email and URLs for Chapa

    $frontendUrl = env('FRONTEND_URL');
    if (empty($frontendUrl) || !filter_var($frontendUrl, FILTER_VALIDATE_URL)) {
        $frontendUrl = url(''); // fallback to app base URL
    }

    $callbackUrl = route('payment.callback');

    try {
        $response = Http::withToken($chapaKey)
            ->post('https://api.chapa.co/v1/transaction/initialize', [
                'amount' => $totalAmount,
                'currency' => 'ETB',
                'email' => $email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'tx_ref' => $tx_ref,
                'callback_url' => $callbackUrl,
                'return_url' => $frontendUrl . '/payment-success',
            ]);

        $body = $response->json();

        if (!isset($body['status']) || $body['status'] !== 'success') {
            $errorMsg = is_array($body['message']) ? json_encode($body['message']) : ($body['message'] ?? 'Unknown error from Chapa');
            \Log::error('Payment initialization failed: ' . $errorMsg, ['tx_ref' => $tx_ref]);

            return response()->json(['message' => 'Payment initialization failed: ' . $errorMsg], 500);
        }

        return response()->json([
            'checkout_url' => $body['data']['checkout_url'],
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Chapa payment initialization error: ' . $e->getMessage());

        return response()->json([
            'message' => 'Payment initialization error: ' . $e->getMessage(),
        ], 500);
    }
}

public function callback(Request $request)
{
    $tx_ref = $request->tx_ref;

    $payment = Payment::where('tx_ref', $tx_ref)->first();

    if (!$payment) {
        return response()->json(['message' => 'Payment not found'], 404);
    }

    $response = Http::withToken(env('CHAPA_SECRET_KEY'))
        ->get("https://api.chapa.co/v1/transaction/verify/{$tx_ref}");

    $body = $response->json();

    $frontendUrl = env('FRONTEND_URL');
    if (empty($frontendUrl) || !filter_var($frontendUrl, FILTER_VALIDATE_URL)) {
        $frontendUrl = url(''); // fallback to app base URL
    }

    if (($body['status'] ?? null) === 'success' && ($body['data']['status'] ?? null) === 'success') {
        $payment->status = 'success';
        $payment->save();

        $reservation = $payment->reservation;
        if ($reservation) {
            $reservation->status = 'confirmed';
            $reservation->save();
        }

        return response()->json([
            'message' => 'Payment successful',
            'return_url' => $frontendUrl . '/payment-success',
        ]);
    } else {
        $payment->status = 'failed';
        $payment->save();

        return response()->json([
            'message' => 'Payment failed or pending',
            'redirect_url' => $frontendUrl . '/payment-failed',
        ]);
    }
}

}
