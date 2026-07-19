# Air Concierge — Migration inventory (Phase 0)

**Status:** Checklist only — no ports  
**Source:** `/mnt/e/xampp/htdocs/airconcierge` (read-only)  
**Generated:** 2026-07-12

Use this as a tracking inventory for Phases 1–6. Do not treat checkboxes as permission to port; follow `migration-plan.md` phase order.

---

## Schema

| Item | Location / note | L13 status |
|------|-----------------|------------|
| Fresh baseline (`migrations_fresh`) | Documented in migration plan as ~101 files under reference `database/migrations_fresh/` | **Not present** on disk at inventory time — re-verify before schema port; **do not copy/run** until approved |
| Legacy incremental migrations | `database/migrations/` (~51 historical) | Not the L13 baseline |
| L13 framework defaults | `airconcierge13/database/migrations/` (users/cache/jobs) | Boot only |

---

## Controllers

**Fat-controller flags** (from migration plan): AjaxDashboardController, BookingController, ChronologycronController, PropertyController, PaymentController, CreateBookingController, CronJobsController, HostawayController, ReportController.

### Admin/ (54)

- [ ] AdminController
- [ ] AirBnbTestEmailsController
- [ ] AjaxDashboardController **(fat)**
- [ ] BookingAlertsController
- [ ] BookingController **(fat)**
- [ ] ChronologyController
- [ ] ChronologycronController **(fat)**
- [ ] CleanerController
- [ ] CleanerViewController
- [ ] CmsController
- [ ] CockpitController
- [ ] CountryController
- [ ] CronJobsController **(fat)**
- [ ] Dashboard
- [ ] DatabaseEntryLogsController
- [ ] DocumentlistController
- [ ] DocumentsRegionsUpdateController
- [ ] GuestController
- [ ] GuestLocationController
- [ ] HostawayLogsController
- [ ] ImportDataController
- [ ] ImportedEmailsController
- [ ] InsuranceController
- [ ] ManagementFeeController
- [ ] ManagementTypeController
- [ ] ManagersController
- [ ] NewftpController
- [ ] OpportunityCostController
- [ ] OutboundEmailLogsController
- [ ] OwnerBlockAbandonmentController
- [ ] OwnerController
- [ ] OwnerTermsAgreementController
- [ ] PaymentController **(fat)**
- [ ] PaymentTypeController
- [ ] PlatformController
- [ ] PropertyAuditController
- [ ] PropertyController **(fat)**
- [ ] PropertyPaymentsController
- [ ] PropertyRevenueSettingsController
- [ ] RegionController
- [ ] ReportController **(fat)**
- [ ] ResourceController
- [ ] RevenueSettingsController
- [ ] RoleController
- [ ] SendemailsController
- [ ] SystemSettingsController
- [ ] TemplateController
- [ ] TemplatesRegionsUpdateController
- [ ] TotReportController
- [ ] UploadDocumentController
- [ ] UserController
- [ ] ZohoSignController
- [ ] ZohoSignControllerTest *(test/helper file in Controllers — review)*
- [ ] coronology_backup.php *(backup/orphan — review)*

### Api/ (8)

- [ ] BookingCancellationController
- [ ] CloudBackupCronController
- [ ] CreateBookingController **(fat)**
- [ ] CreateBookingControllerTest *(test/helper file — review)*
- [ ] CronJobsController **(fat)**
- [ ] DropboxFormCSVController
- [ ] GuestBookingsController
- [ ] OwnerEmailNotificationController

### Auth/ (2)

- [ ] AuthController
- [ ] PasswordController

### Root (4)

- [ ] Controller *(base)*
- [ ] HostawayController **(fat)**
- [ ] OwnerRegistrationController
- [ ] Welcome

---

## Cron / HTTP scheduled routes

**Phase 1 (greenfield):** no public `/cron/*` HTTP in L13. Artisan command stubs + Schedule only. Mapping below is for cutover awareness (legacy URI → new command name).

### `/cron/*` (Api) → L13 command stubs

| Legacy URI | Legacy action | L13 command (stub) |
|------------|---------------|--------------------|
| `/cron/test` | Api\CronJobsController@test | `cron:test` |
| `/cron/check-recurring-payments` | Api\CronJobsController@checkRecurringPayments | `alert:recurring-payments` |
| `/cron/check-user-passwords-expiry` | Api\CronJobsController@checkPasswordsExpiry | `alert:password-expiry` |
| `/cron/check-property-permit-expiry` | Api\CronJobsController@checkPropertyPermitExpiry | `alert:property-permit-expiry` |
| `/cron/check-property-vacancy` | Api\CronJobsController@checkVacantProperties | `alert:property-vacancy` |
| `/cron/check-booking-month-difference` | Api\CronJobsController@checkbookingMonthDifference | `alert:booking-month-difference` |
| `/cron/check-consecutive-guest-bookings` | Api\CronJobsController@checkGuestConsecutiveBookings | `alert:consecutive-guest-bookings` |
| `/cron/check-policy-expiry-email` | Api\CronJobsController@checkInsurancePolicyExpiries | `alert:insurance-policy-expiry` |
| `/cron/check-anti-gap-alert` | Api\CronJobsController@checkAntiGapAlert | `alert:anti-gap` |
| `/cron/cloud/weekly-data-backup` | Api\CloudBackupCronController@uploadFilesToGoogleDrive | `cloud:weekly-data-backup` |
| `/cron/check-security-deposit-alerts` | Api\CronJobsController@checkSecurityDepositAlerts | `alert:security-deposit` |
| `/cron/check-owner-block-reminders` | Api\CronJobsController@checkOwnerBlockReminders | `alert:owner-block-reminders` |
| `/cron/check-owner-block-extensions` | Api\CronJobsController@checkOwnerBlockExtensions | `alert:owner-block-extensions` |
| `/cron/check-property-audits` | Api\CronJobsController@checkPropertyAudits | `alert:property-audits` |
| `/cron/check-property-audit-reminders` | Api\CronJobsController@checkPropertyAuditReminders | `alert:property-audit-reminders` |
| `/cron/business-license-expiry` | Api\CronJobsController@checkBusinessLicenseExpiry | `alert:business-license-expiry` |
| `/cron/check-booking-conflicts` | Api\CronJobsController@checkBookingConflicts | `alert:booking-conflict` |
| `/cron/dropboxformcsv` | Api\DropboxFormCSVController@to_get_workflow_instance | `dropbox:form-csv` |
| `/cron/dropboxformsyncdb` | Api\DropboxFormCSVController@csv_read_sync_db | `dropbox:form-sync-db` |
| `/cron/dropboxformstatusupdate` | Api\DropboxFormCSVController@to_get_workflow_owner_list | `dropbox:form-status-update` |

Also scheduled from former Artisan Kernel (not HTTP `/cron/*`): `emails:process-reviews`, `owners:payout`, `property:monthly-metrics` (+ `--full` weekly), `owner-block-abandonments:resolve`, `images:compress-uploads`, `emails:cleanup-outbound-logs`, `emails:limit-owner-block`.

### Chronology / related

| URI | Controller@method |
|-----|-------------------|
| `chronologycron` | Admin\ChronologycronController@index |
| `chronologycronnew` | Admin\ChronologycronController@chronologycronnew |
| `chronologycronemail` | Admin\ChronologycronController@email_send |
| `emailmessage_opened/{id}` | Admin\ChronologycronController@emailmessage_opened |
| `mailsend_document` (POST) | Admin\ChronologycronController@mailsend_document |
| `email_opened/{id}` | Admin\ChronologycronController@email_opened |
| `signdocument` | Admin\ChronologycronController@signdocument |
| `hellosigndocument` | Admin\ChronologycronController@hellosigndocument |
| `testemail` | Admin\ChronologycronController@testemail |
| `toCompleteZohoSignDocument` | Admin\ChronologycronController@toCompleteZohoSignDocument |
| `getZohoSingleDocumentList` | Admin\ChronologycronController@getZohoSingleDocumentList |
| `zohoSignedDocumentDownload/{id}/{ids}` | Admin\ChronologycronController@getZohoSignedDocumentDownload |
| admin `cron` settings | Admin\CronJobsController@viewCronSettings |

---

## Composer packages → L13 intent

| Current package | Action |
|-----------------|--------|
| `laravel/framework` 5.1.* | Fresh L13 (`^13`) |
| `zizaco/entrust` | Spatie Permission + Policies/Gates |
| `yajra/laravel-datatables-oracle` ~5 | Upgrade to current Yajra for L13 |
| `sammyk/laravel-facebook-sdk` | Verify still needed; remove or replace |
| `guzzlehttp/guzzle` ~6 | Guzzle 7+ (via framework) |
| `flynsarmy/csv-seeder` | Revisit — modern seeders / import commands |
| `vinkla/hashids` | Verify L13 compatibility or replace |
| `niklasravnsborg/laravel-pdf` + wkhtmltopdf binaries | Spike DomPDF / Browsershot / maintained wrapper (ADR-005) |
| `phpmailer/phpmailer` | Laravel Mail / Mailables |
| `nao-pon/flysystem-google-drive` | Flysystem v3 + Laravel filesystem |
| `hellosign/hellosign-php-sdk` | Wrap in service; verify Zoho vs HelloSign status |
| `jeremykenedy/slack-laravel` | Laravel Slack notification channel |
| `doctrine/dbal` | Keep if needed; pin compatible version |
| `filp/whoops` | Not needed on L13 |

---

## Helpers (`app/helpers.php`)

~182 top-level functions. Goal (Phase 6): eliminate Composer `files` autoload; move to services / support / view layer.

### Date / formatting

`convert_mysql_format`, `convert_mysql_datetime_format`, `convert_mysql_time_format`, `us_date_format`, `us_datetime_format`, `us_time_format`, `cms_date_format`, `cms_datetime_format`, `user_date_format`, `user_datetime_format`, `addMonths`, `years_list`, `years_list_options`, `Prev_years_list`, `months_list`, `months_list_options`, `months_shortnames_list`, `get_month_name`, `last_twelve_months`, `next_twelve_months`, `get_month_last_date`, `get_last_twelve_months_array`, `get_array_btw_two_dates`, `calculate_average_date`, `calculate_no_of_nights_between_dates`, `getLastDayOfMonth`, `BookingYearsOptions`, `isMonthClosed`

### JSON / HTTP / response helpers

`bind_json_array`, `bind_json_error`, `bind_json_response`, `generateJsonResponse`, `encodeURIComponent`, `getClientIpAddress`, `getLastUriSegement`, `base_url`, `viewfailUploads`

### Lists / selects (UI)

`list_regions`, `list_subregions`, `all_subregions`, `list_all_subregions`, `list_region_properties`, `ajax_list_region_properties`, `list_all_region_properties`, `list_all_region_selected_properties`, `list_region_properties_json`, `list_all_properties`, `list_all_properties_json`, `list_bookings_properties`, `list_owner_role_properties`, `list_manager_properties`, `list_manager_regions`, `list_property_bookings`, `list_regional_managers`, `list_all_regional_managers`, `list_selected_region_id_properties`, `list_platforms`, `list_payment_types`, `list_payment_type_categories`, `list_incoming_payment_types`, `list_guests`, `list_us_states`, `list_contract_end_reasons`, `list_cleaners`, `listAllVendors`, `ManagementTypeOptions`, `render_menu`

### Property / owner / manager domain

`getPropertyOwners`, `getOwnersEmails`, `getPropertyManagers`, `getManagerProperties`, `getManagerRegions`, `getManagersEmail`, `getOwnerProperties`, `hasActiveProperty`, `getPropertyStatusString`, `getPropertyAuditStatusString`, `getPropertyLimit`, `getPropertyOnlineListings`, `getPropertyOccupancy`, `getChildBookingsOccupancy`, `propertiesWtihExpiringPermits`, `removeCommonPropertyTitlesPart`

### Booking / payment / fee calculations

`calculateBookingIncome`, `calculateBookingExpense`, `calculateNegativeOwnerPayout`, `calculateGrossOwnerPayoutAmount`, `calculateNetOwnerPayoutAmount`, `calculateSiteListingFee`, `calculateOffsitePropertyManagementFee`, `calculateTotalPropertyPayments`, `getBookingManagementFeePercentage`, `getManagementFeeAsAccomodations`, `getStaffCommissionAsManagementFee`, `getMaxPropertyMgmtFeeRules`, `getPropertyMgmtFeeRules`, `getMgmtFeeRuleValue`, `formatMgmtFeeRuleForExport`, `getSafelyInsuranceFee`, `getAccommodationPerChannel`, `getPossibleBookingSplits`, `getOriginalBookingDates`, `getBookingGuests`, `getBookingEditUrl`, `filter_specific_platform_bookings`, `isTotCalculationRequired`, `translate_tot_mode`, `translate_vrbo_tot_mode`, `getCancelledBookingText`, `getPaymentClearText`, `getPaymentReceipts`, `getPropertiesWithPayout`, `markBookingsAsPaymentDisbursed`, `markBookingsAsPaymentDisbursedForProperty`, `getPaymentClearBookingsQuery`, `getLastRecurringBookingPayment`, `isLastRecurringBookingPayment`, `getLastRecurringPropertyPayment`, `isLastRecurringPropertyPayment`, `isRecurringPropertyPayment`, `isRecurringBookingPayment`, `isRecurringPayment`, `isOwnerBlockModificationSafe`, `ownerBlockRestrictedModification`, `getBookingFeeBoolean`, `getBookingFeePercentage`

### Email / SMTP / notifications

`sendSmtpEmail`, `processEmailById`, `count_pending_processed_emails`, `sendPasswordExpiryEmail`, `sendPropertyLimitEmailNotification`, `sendEditSummaryEmail`, `sendCreateOnMonthClosedEmail`, `sendDeleteEmail`, `notifyDeveloper`

### Auth / password / roles

`get_role_base_resources`, `isOldPassword`, `generateRandomString`, `generate_random_token`, `randomString`

### String / array / misc utilities

`fetchBefore`, `fetchAfter`, `fetchBetween`, `fetchAllBetween`, `removeNewlines`, `removeTags`, `cleanStringForStoring`, `cleanStringForReading`, `clean_decimal_value`, `formatDollar`, `nice_number`, `nice_number_million`, `nice_number_thousand`, `sortAssociativeArrayByKey`, `arrayExists`, `getRegionArrayIndex`, `getArrayDifferences`, `calculatePercentageChange`, `redactName`, `userFriendlyFieldName`, `export_array_to_csv`, `cms_delete_record`, `getColumnValue`, `get_validation_group_rule`, `getUserTableCurrentState`, `getUserSavedFilters`, `make_chart_json`, `get_filetype_icons`, `total_users`, `zipLookup`, `zipLookupGoogleMap`, `get_aplicable_platforms`, `get_not_aplicable_platforms`, `translate_properties_additional_fee_type`, `translate_properties_additional_fee_application`, `getAdditionalInsuranceName`, `getSalaryType`, `getEmploymentStatus`, `getHiredBy`, `getCleanerPosition`, `getStatus`, `getLatestActionColumn`, `checkZohoSignTokenExpireStatus`

---

## Phase mapping (reminder)

| Phase | Focus |
|-------|--------|
| 0 | Foundation (this inventory) |
| 1 | Routing & auth |
| 2 | Hostaway + webhooks |
| 3 | Email / Chronology |
| 4 | Bookings & Payments |
| 5 | Reports & Dashboard |
| 6 | Remaining admin, helpers, packages, deployment readiness |
