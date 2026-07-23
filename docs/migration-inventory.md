# Air Concierge — Migration inventory (Phase 0)

**Status:** Checklist only — no ports  
**Source:** `legacy/` (gitignored in-repo snapshot; business-behavior reference only). Original tree: `/mnt/e/xampp/htdocs/airconcierge` (read-only).  
**Generated:** 2026-07-12  

Use this as a tracking inventory for Phases 1–6 (including Phase 1a). Do not treat checkboxes as permission to port; follow `migration-plan.md` phase order (requirements: `Laravel_5.1_to_13_Modernization_Spec.md`). Implementation follows Spec / plan / ADRs — not legacy technical patterns.

---

## Schema

| Item | Location / note | L13 status |
|------|-----------------|------------|
| Fresh baseline (`migrations_fresh`) | `legacy/database/migrations_fresh/` (~101 files); also mirrored at `database/migrations_fresh/` | **Reference only** — **do not copy/run** into live migrations until approved |
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

Phase 1a audit (2026-07-19). Canonical ownership matrix: [`migration-plan.md`](migration-plan.md) §10.

| Current package | Action | Status | Ownership |
|-----------------|--------|--------|-----------|
| `laravel/framework` 5.1.* | Fresh L13 (`^13`) | **replaced** | Phase 0 (done) |
| `zizaco/entrust` | Spatie Permission + Policies/Gates | **replaced** | Phase 1 (done) |
| `yajra/laravel-datatables-oracle` ~5 | Upgrade to current Yajra for L13 | deferred | Phase 5 |
| `sammyk/laravel-facebook-sdk` | Verify still needed; remove or replace | verify-then-remove/replace | Phase 2.5 |
| `guzzlehttp/guzzle` ~6 | Guzzle 7+ (via framework; confirm at Hostaway port) | deferred | Phase 2.1 |
| `flynsarmy/csv-seeder` | Revisit — modern seeders / import commands | deferred | Phase 2.5 |
| `vinkla/hashids` | Verify L13 compatibility or replace | verify-then-replace | Phase 2.5 |
| `niklasravnsborg/laravel-pdf` + wkhtmltopdf binaries | Spike DomPDF / Browsershot / maintained wrapper (ADR-005) | deferred (spike) | ADR-005 → Phase 2.3/2.4 + 4 |
| `phpmailer/phpmailer` | Laravel Mail / Mailables | deferred | Phase 2.2 |
| `nao-pon/flysystem-google-drive` | Flysystem v3 + Laravel filesystem | deferred | Phase 2.5 |
| `hellosign/hellosign-php-sdk` | Wrap in service; verify Zoho vs HelloSign status | deferred | Phase 2.2 |
| `jeremykenedy/slack-laravel` | Laravel Slack notification channel | deferred | Phase 4 |
| `doctrine/dbal` | Keep if needed; pin compatible version | as-needed | Schema work |
| `filp/whoops` | Not needed on L13 | not needed | N/A on L13 |

---

## Helpers (`legacy/app/helpers.php`) — Phase 3 disposition

**Count:** 181 top-level functions in `legacy/app/helpers.php` (Spec/plan “~100” is shorthand).  
**L13 status:** No `app/helpers.php`; Composer has **no** `files` autoload for helpers (already clean).  
**Policy:** [ADR-015](adr/015-helpers-support-disposition.md). Disposition values: `implemented` | `superseded` | `deferred` | `delete-candidate`.

Inventory drift (listed historically, **not** top-level in current `helpers.php`): `getAccommodationPerChannel`, `markBookingsAsPaymentDisbursedForProperty`.

### Date / formatting

| Function | Destination | Disposition |
|----------|-------------|-------------|
| `convert_mysql_format` | `App\Support\Date\DateFormatter::toMysqlDate` | implemented |
| `convert_mysql_datetime_format` | `DateFormatter::toMysqlDateTime` | implemented |
| `convert_mysql_time_format` | `DateFormatter::toMysqlTime` | implemented |
| `us_date_format` | `DateFormatter::usDate` | implemented |
| `us_datetime_format` | `DateFormatter::usDateTime` | implemented |
| `us_time_format` | `DateFormatter::usTime` | implemented |
| `cms_date_format` | `DateFormatter::cmsDate` | implemented |
| `cms_datetime_format` | `DateFormatter::cmsDateTime` | implemented |
| `user_date_format` | `DateFormatter::userDate` | implemented |
| `user_datetime_format` | `DateFormatter::userDateTime` | implemented |
| `calculate_no_of_nights_between_dates` | `App\Support\Date\DateMath::nightsBetween` | implemented |
| `get_month_last_date` | `DateMath::monthDateRange` | implemented |
| `getLastDayOfMonth` | `DateMath::lastDayOfMonth` | implemented |
| `addMonths` | `DateMath` (extend when needed) | deferred |
| `years_list`, `years_list_options`, `Prev_years_list` | Phase 5 select builders / Support | deferred |
| `months_list`, `months_list_options`, `months_shortnames_list`, `get_month_name` | Phase 5 / Support | deferred |
| `last_twelve_months`, `next_twelve_months`, `get_last_twelve_months_array` | Reports/Dashboard or Support | deferred |
| `get_array_btw_two_dates`, `calculate_average_date` | `DateMath` when report consumers land | deferred |
| `BookingYearsOptions` | Phase 5 Blade / booking forms | deferred |
| `isMonthClosed` | MonthClosing domain model/query (**not** DateFormatter) | deferred |

### Money / string cleaning

| Function | Destination | Disposition |
|----------|-------------|-------------|
| `formatDollar` | `App\Support\Money\MoneyFormatter::dollar` | implemented |
| `nice_number` | `MoneyFormatter::abbreviated` | implemented |
| `nice_number_million` | `MoneyFormatter::asMillions` | implemented |
| `nice_number_thousand` | `MoneyFormatter::asThousands` | implemented |
| `cleanStringForStoring` | `App\Support\String\StringCleaner::forStoring` | implemented |
| `cleanStringForReading` | `StringCleaner::forReading` | implemented |
| `clean_decimal_value` | `StringCleaner::decimal` | implemented |
| `fetchBefore`, `fetchAfter`, `fetchBetween`, `fetchAllBetween` | `App\Support\String\TextExtract` when needed | deferred |
| `removeNewlines`, `removeTags` | `StringCleaner` extend when needed | deferred |

### JSON / HTTP / response

| Function | Destination | Disposition |
|----------|-------------|-------------|
| `bind_json_array`, `bind_json_error`, `bind_json_response`, `generateJsonResponse` | Laravel JSON responses / API Resources | deferred / delete-candidate |
| `encodeURIComponent` | JS/`rawurlencode` at call site | delete-candidate |
| `getClientIpAddress` | `$request->ip()` | superseded |
| `getLastUriSegement` | route / request path helpers | deferred |
| `base_url` | `url()` / `config('app.url')` | superseded |
| `viewfailUploads` | Phase 5 upload UI | deferred |

### Lists / selects (UI) — all deferred to Phase 5

`list_regions`, `list_subregions`, `all_subregions`, `list_all_subregions`, `list_region_properties`, `ajax_list_region_properties`, `list_all_region_properties`, `list_all_region_selected_properties`, `list_region_properties_json`, `list_all_properties`, `list_all_properties_json`, `list_bookings_properties`, `list_owner_role_properties`, `list_manager_properties`, `list_manager_regions`, `list_property_bookings`, `list_regional_managers`, `list_all_regional_managers`, `list_selected_region_id_properties`, `list_platforms`, `list_payment_types`, `list_payment_type_categories`, `list_incoming_payment_types`, `list_guests`, `list_us_states`, `list_contract_end_reasons`, `list_cleaners`, `listAllVendors`, `ManagementTypeOptions`, `render_menu`

**Destination:** Blade components / View composers / Livewire — **not** `App\Support`. Use Eloquent scopes (e.g. `Region::notDeleted()`).

### Property / owner / manager

| Function | Destination | Disposition |
|----------|-------------|-------------|
| `hasActiveProperty` | `User::hasActiveAccess()` (ADR-009/012) | superseded |
| `getPropertyOwners`, `getOwnersEmails`, `getOwnerProperties` | Owner/Property relationships / services | deferred |
| `getPropertyManagers`, `getManagerProperties`, `getManagerRegions`, `getManagersEmail` | Manager domain when ported | deferred |
| `getPropertyStatusString`, `getPropertyAuditStatusString` | Property model / enums | deferred |
| `getPropertyLimit`, `getPropertyOnlineListings`, `getPropertyOccupancy`, `getChildBookingsOccupancy` | PropertyService / ReportService | deferred |
| `propertiesWtihExpiringPermits` | PropertyService / scheduled alert | deferred |
| `removeCommonPropertyTitlesPart` | Property model or Support string util | deferred |

### Booking / payment / fee calculations — deferred

Destination: `Booking`/`Payment` model methods or `BookingService`/`PaymentService` when payout/report parity PRs land (not Phase 3 Support).

`calculateBookingIncome`, `calculateBookingExpense`, `calculateNegativeOwnerPayout`, `calculateGrossOwnerPayoutAmount`, `calculateNetOwnerPayoutAmount`, `calculateSiteListingFee`, `calculateOffsitePropertyManagementFee`, `calculateTotalPropertyPayments`, `getBookingManagementFeePercentage`, `getManagementFeeAsAccomodations`, `getStaffCommissionAsManagementFee`, `getMaxPropertyMgmtFeeRules`, `getPropertyMgmtFeeRules`, `getMgmtFeeRuleValue`, `formatMgmtFeeRuleForExport`, `getSafelyInsuranceFee`, `getPossibleBookingSplits`, `getOriginalBookingDates`, `getBookingGuests`, `getBookingEditUrl`, `filter_specific_platform_bookings`, `isTotCalculationRequired`, `translate_tot_mode`, `translate_vrbo_tot_mode`, `getCancelledBookingText`, `getPaymentClearText`, `getPaymentReceipts`, `getPropertiesWithPayout`, `markBookingsAsPaymentDisbursed`, `getPaymentClearBookingsQuery`, `getLastRecurringBookingPayment`, `isLastRecurringBookingPayment`, `getLastRecurringPropertyPayment`, `isLastRecurringPropertyPayment`, `isRecurringPropertyPayment`, `isRecurringBookingPayment`, `isRecurringPayment`, `isOwnerBlockModificationSafe`, `ownerBlockRestrictedModification`, `getBookingFeeBoolean`, `getBookingFeePercentage`

### Email / SMTP / notifications

| Function | Destination | Disposition |
|----------|-------------|-------------|
| `sendSmtpEmail` | `EmailService` + `SendOutboundEmailJob` (ADR-004) | superseded |
| `processEmailById` | Import redesign — legacy curl-to-cron is dead | delete-candidate |
| `count_pending_processed_emails` | Chronology/Import query | deferred |
| `sendPasswordExpiryEmail`, `sendPropertyLimitEmailNotification`, `sendEditSummaryEmail`, `sendCreateOnMonthClosedEmail`, `sendDeleteEmail` | Phase 4 Mailables / notification jobs | deferred |
| `notifyDeveloper` | Laravel notification / log channel | deferred |

### Auth / password / roles

| Function | Destination | Disposition |
|----------|-------------|-------------|
| `get_role_base_resources` | `UserRole` + Policies (ADR-010) — no Entrust | delete-candidate |
| `isOldPassword` | Password history when ported | deferred |
| `generateRandomString`, `generate_random_token`, `randomString` | `Str::random` / framework | superseded |

### String / array / misc

| Function | Destination | Disposition |
|----------|-------------|-------------|
| `sortAssociativeArrayByKey`, `arrayExists`, `getRegionArrayIndex`, `getArrayDifferences`, `calculatePercentageChange` | Support collections util or inline | deferred |
| `redactName`, `userFriendlyFieldName` | Support / presentation | deferred |
| `export_array_to_csv` | `ImportService` / report export | deferred |
| `cms_delete_record`, `getColumnValue` | Eloquent — do not re-port raw DB helpers | delete-candidate |
| `get_validation_group_rule` | Form Requests | deferred |
| `getUserTableCurrentState`, `getUserSavedFilters` | Phase 5 DataTables prefs | deferred |
| `make_chart_json` | DashboardService / Phase 5 | deferred |
| `get_filetype_icons` | Blade / assets | deferred |
| `total_users` | User query — not a helper | delete-candidate |
| `zipLookup`, `zipLookupGoogleMap` | Geocoding service when needed | deferred |
| `get_aplicable_platforms`, `get_not_aplicable_platforms` | Platform model / config | deferred |
| `translate_properties_additional_fee_*`, `getAdditionalInsuranceName`, `getSalaryType`, `getEmploymentStatus`, `getHiredBy`, `getCleanerPosition`, `getStatus`, `getLatestActionColumn` | Enums / model labels | deferred |
| `checkZohoSignTokenExpireStatus` | `ZohoSignService` | superseded / deferred thin method |

---

## Phase mapping (reminder)

| Phase | Focus |
|-------|--------|
| 0 | Foundation (this inventory) |
| 1 | Routing & Middleware / auth scaffold |
| 1a | Early-stage gap closure (Hostaway signatures + package ownership audit) |
| 2 | Extract Services (2.1 Hostaway → 2.2 Email/Chronology → 2.3 Bookings & Payments → 2.4 Reports & Dashboard → 2.5 Remaining admin) |
| 3 | Helpers Decomposition |
| 4 | Async Migration (jobs, workers, failed-job monitoring) |
| 5 | Frontend / Views (incremental Blade + Yajra DataTables) |
| 6 | Deployment Readiness |
