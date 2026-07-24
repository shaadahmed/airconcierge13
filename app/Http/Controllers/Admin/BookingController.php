<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bookings\StoreBookingRequest;
use App\Http\Requests\Admin\Bookings\UpdateBookingRequest;
use App\Models\Booking;
use App\Services\Bookings\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = $this->bookingService->list($request->only(['property_id', 'cancelled']));

        return response()->json(['data' => $bookings]);
    }

    public function show(Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['property', 'guests', 'payments']);

        return response()->json(['data' => $booking]);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create($request->validated());

        return response()->json(['data' => $booking], 201);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        $booking = $this->bookingService->update($booking, $request->validated());

        return response()->json(['data' => $booking]);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $this->authorize('delete', $booking);

        $this->bookingService->delete($booking);

        return response()->json(status: 204);
    }

    public function cancel(Booking $booking): JsonResponse
    {
        $this->authorize('update', $booking);

        $booking = $this->bookingService->cancel($booking);

        return response()->json(['data' => $booking]);
    }
}
