<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;

/**
 * Seeds active sidebar resources from the legacy resources dump.
 * Soft-deleted rows (id 175 Payments, id 191 Guest Location duplicate) are omitted.
 */
class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [12, 0, 'Dashboard', '#', 'User Dashboard', 0, 0, 100, 'fas fa-tachometer-alt', 0],
            [170, 181, 'Guests', 'admin/guests', '', 1, 0, 10, '', 0],
            [171, 182, 'Bookings', 'admin/bookings', '', 1, 0, 20, '', 0],
            [168, 181, 'Owners', 'admin/owners', 'Manage Owners', 1, 0, 10, '', 0],
            [169, 181, 'Homes', 'admin/properties', '', 1, 0, 1, '', 0],
            [172, 0, 'Reporting', 'admin/reports', '', 0, 0, 600, 'fas fa-chart-bar', 0],
            [173, 172, 'Reporting', 'admin/reports/properties', '', 1, 0, 10, '', 0],
            [174, 172, 'Nights & Pay Outs', 'admin/reports/nightspayouts', '', 1, 0, 20, '', 0],
            [176, 182, 'Costs & Expenses', 'admin/payments/bookings', '', 1, 0, 30, '', 0],
            [177, 181, 'Regions', 'admin/regions', '', 1, 0, 30, '', 0],
            [178, 183, 'Vendors', 'admin/vendors', '', 1, 0, 10, '', 0],
            [179, 183, 'Regional Managers', 'admin/managers', '', 1, 0, 20, '', 0],
            [180, 12, 'Performance', 'admin/dashboard', '', 1, 0, 10, '', 0],
            [181, 0, 'Properties', '#', '', 0, 0, 200, 'fa fa-users', 0],
            [182, 0, 'Financials', '#', '', 0, 0, 300, 'fa fa-book', 0],
            [183, 0, 'Staff', '#', '', 0, 0, 400, 'fas fa-user-tie', 0],
            [184, 172, 'Difference (Booking vs Stay)', 'admin/reports/bookingstay', '', 1, 0, 30, '', 0],
            [185, 172, 'Total Income', 'admin/reports/totalincome', '', 1, 0, 40, '', 0],
            [186, 172, 'Regions Income', 'admin/reports/regionsincome', '', 1, 0, 35, '', 0],
            [187, 182, 'P&L Owner Statements', 'admin/dashboard/ownerstatements', '', 1, 0, 200, '', 0],
            [188, 12, 'Calendar Blocks (Owners)', 'admin/dashboard/ownerblock/all', '', 1, 0, 300, '', 0],
            [189, 12, 'Accounting', 'admin/dashboard/accounting', '', 1, 0, 20, '', 0],
            [190, 12, 'Calendar View', 'admin/dashboard/ownercalendarview', '', 1, 0, 400, '', 0],
            [192, 172, 'Guest Location', 'admin/reports/guestlocation', 'Guest Travel Map', 1, 0, 40, '', 0],
            [193, 172, 'Reservation | City Limit', 'admin/reports/properties/threshold', 'Page to see entire portfolio of properties that are nearing some threshold.', 1, 0, 99, '', 0],
            [194, 12, 'Reservation | City Limit', 'admin/dashboard/threshold-properties', 'List of properties, reaching/crossing regional limitation threshold', 1, 0, 401, '', 0],
            [195, 182, 'Cash Flow', 'admin/dashboard/cashflow', '', 1, 0, 402, '', 0],
            [196, 182, 'Revenue Settings', 'admin/settings/revenue', 'Revenue Settings For Properties', 1, 0, 31, '', 0],
            [197, 181, 'Audit Reports', 'admin/properties/audit/list', 'List of audits performed on different properties', 1, 0, 20, '', 0],
            [198, 182, 'Month Closing History', 'admin/reports/properties/closing-history', 'Month closing history of properties', 1, 0, 201, '', 0],
            [199, 181, 'Countries & States', 'admin/countries', '', 1, 0, 40, 'fa fa-globe', 0],
            [200, 172, 'Owner Block Abandonment', 'admin/reports/owner-block-abandonment', '', 1, 0, 100, '', 0],
        ];

        foreach ($rows as $row) {
            Resource::query()->updateOrCreate(
                ['id' => $row[0]],
                [
                    'parent_id' => $row[1],
                    'name' => $row[2],
                    'path' => $row[3],
                    'description' => $row[4],
                    'link' => $row[5],
                    'external_link' => $row[6],
                    'order' => $row[7],
                    'icon_class' => $row[8] !== '' ? $row[8] : null,
                    'deleted' => $row[9],
                ]
            );
        }
    }
}
