<?php

namespace App\Enums;

/**
 * Canonical application roles (single source of truth for role string values).
 */
enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case Manager = 'manager';
    case Owner = 'owner';
    case Cleaner = 'cleaner';
    case Maintenance = 'maintenance';
}
