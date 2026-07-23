<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bookings\StoreBookingRequest;
use App\Http\Requests\Admin\Bookings\UpdateBookingRequest;
use App\Models\Booking;
use App\Services\Bookings\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = $this->bookingService->list($request->only(['property_id', 'cancelled']));

        if ($request->wantsJson()) {
            return response()->json(['data' => $bookings]);
        }

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View|JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['property', 'guests', 'payments']);

        if (request()->wantsJson()) {
            return response()->json(['data' => $booking]);
        }

        return view('admin.bookings.show', compact('booking'));
    }

    public function store(StoreBookingRequest $request): RedirectResponse|JsonResponse
    {
        $booking = $this->bookingService->create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $booking], 201);
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', 'Booking created.');
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $booking = $this->bookingService->update($booking, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $booking]);
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', 'Booking updated.');
    }

    public function destroy(Booking $booking): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $booking);

        $this->bookingService->delete($booking);

        if (request()->wantsJson()) {
            return response()->json(status: 204);
        }

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', 'Booking deleted.');
    }

    public function cancel(Booking $booking): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $booking);

        $booking = $this->bookingService->cancel($booking);

        if (request()->wantsJson()) {
            return response()->json(['data' => $booking]);
        }

        return back()->with('status', 'Booking cancelled.');
    }
}
