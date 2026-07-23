<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BookingPayment;
use App\Models\PropertyPayment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, BookingPayment|PropertyPayment $payment): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, BookingPayment|PropertyPayment $payment): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, BookingPayment|PropertyPayment $payment): bool
    {
        return $this->isStaff($user);
    }

    private function isStaff(User $user): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Manager => true,
            default => false,
        };
    }
}
