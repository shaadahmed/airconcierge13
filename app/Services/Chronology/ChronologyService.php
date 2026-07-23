<?php

namespace App\Services\Chronology;

use App\Models\Chronology;
use App\Models\ChronologyMail;
use App\Models\ChronologyOrder;
use App\Models\ChronologyOwnerEmail;
use App\Models\DocumentUpload;
use App\Models\EmailTemplate;
use App\Models\HelloSignDetail;
use App\Models\Owner;
use App\Models\Property;
use App\Services\Email\EmailService;
use App\Services\Zoho\ZohoSignService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChronologyService
{
    public function __construct(
        private EmailService $emailService,
        private ZohoSignService $zohoSignService,
    ) {}

    /**
     * @return Collection<int, Chronology>
     */
    public function list(): Collection
    {
        return Chronology::query()
            ->with(['regions', 'subregions'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  array{name: string, startdate: string, chronologyoption?: int|null, allregion?: int|null, allsubregion?: int|null, region_ids?: list<int>, subregion_ids?: list<int>}  $data
     */
    public function create(array $data): Chronology
    {
        return DB::transaction(function () use ($data): Chronology {
            $chronology = Chronology::query()->create([
                'name' => $data['name'],
                'startdate' => $data['startdate'],
                'chronologyoption' => $data['chronologyoption'] ?? 0,
                'allregion' => $data['allregion'] ?? 0,
                'allsubregion' => $data['allsubregion'] ?? 0,
                'created_date' => now(),
                'update_date' => now(),
            ]);

            $this->syncAudience($chronology, $data['region_ids'] ?? [], $data['subregion_ids'] ?? []);

            return $chronology->load(['regions', 'subregions']);
        });
    }

    /**
     * @param  array{name?: string, startdate?: string, chronologyoption?: int|null, allregion?: int|null, allsubregion?: int|null, region_ids?: list<int>, subregion_ids?: list<int>}  $data
     */
    public function update(Chronology $chronology, array $data): Chronology
    {
        return DB::transaction(function () use ($chronology, $data): Chronology {
            $startChanged = isset($data['startdate'])
                && Carbon::parse($data['startdate'])->toDateString() !== $chronology->startdate?->toDateString();

            $chronology->update([
                'name' => $data['name'] ?? $chronology->name,
                'startdate' => $data['startdate'] ?? $chronology->startdate,
                'chronologyoption' => $data['chronologyoption'] ?? $chronology->chronologyoption,
                'allregion' => $data['allregion'] ?? $chronology->allregion,
                'allsubregion' => $data['allsubregion'] ?? $chronology->allsubregion,
                'update_date' => now(),
            ]);

            if (array_key_exists('region_ids', $data) || array_key_exists('subregion_ids', $data)) {
                $this->syncAudience($chronology, $data['region_ids'] ?? [], $data['subregion_ids'] ?? []);
            }

            if ($startChanged) {
                ChronologyMail::query()->where('chronology_id', $chronology->id)->delete();
                HelloSignDetail::query()->where('chronology_id', $chronology->id)->delete();
            }

            return $chronology->fresh(['regions', 'subregions']) ?? $chronology;
        });
    }

    public function delete(Chronology $chronology): void
    {
        if ($chronology->orders()->exists()) {
            throw new InvalidArgumentException('Cannot delete a chronology that still has order steps.');
        }

        DB::transaction(function () use ($chronology): void {
            $chronology->regions()->detach();
            $chronology->subregions()->detach();
            $chronology->ownerOptOuts()->delete();
            $chronology->delete();
        });
    }

    public function copy(Chronology $chronology): Chronology
    {
        return DB::transaction(function () use ($chronology): Chronology {
            $copy = $this->create([
                'name' => 'Copy-'.$chronology->name,
                'startdate' => $chronology->startdate?->toDateString() ?? now()->toDateString(),
                'chronologyoption' => $chronology->chronologyoption,
                'allregion' => $chronology->allregion,
                'allsubregion' => $chronology->allsubregion,
                'region_ids' => $chronology->regions()->pluck('regions.id')->all(),
                'subregion_ids' => $chronology->subregions()->pluck('subregions.id')->all(),
            ]);

            foreach ($chronology->orders as $order) {
                $this->createOrder($copy, [
                    'day' => $order->day,
                    'time' => $order->time,
                    'minute' => $order->minute,
                    'previousaction' => $order->previousaction,
                    'document_id' => $order->document_id,
                    'template_id' => $order->template_id,
                    'document_name' => $order->document_name,
                    'template_name' => $order->template_name,
                ]);
            }

            return $copy->load(['orders', 'regions', 'subregions']);
        });
    }

    /**
     * @param  array{day: int, time?: string|null, minute?: string|null, previousaction?: string|null, document_id?: int|null, template_id?: int|null, document_name?: string|null, template_name?: string|null}  $data
     */
    public function createOrder(Chronology $chronology, array $data): ChronologyOrder
    {
        return ChronologyOrder::query()->create([
            'chronology_id' => $chronology->id,
            'day' => $data['day'],
            'time' => $data['time'] ?? '09',
            'minute' => $data['minute'] ?? '00',
            'previousaction' => $data['previousaction'] ?? '0',
            'document_id' => $data['document_id'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'document_name' => $data['document_name'] ?? null,
            'template_name' => $data['template_name'] ?? null,
            'created_date' => now(),
            'update_date' => now(),
        ]);
    }

    /**
     * @param  array{day?: int, time?: string|null, minute?: string|null, previousaction?: string|null, document_id?: int|null, template_id?: int|null, document_name?: string|null, template_name?: string|null}  $data
     */
    public function updateOrder(ChronologyOrder $order, array $data): ChronologyOrder
    {
        $order->update([
            'day' => $data['day'] ?? $order->day,
            'time' => $data['time'] ?? $order->time,
            'minute' => $data['minute'] ?? $order->minute,
            'previousaction' => $data['previousaction'] ?? $order->previousaction,
            'document_id' => $data['document_id'] ?? $order->document_id,
            'template_id' => $data['template_id'] ?? $order->template_id,
            'document_name' => $data['document_name'] ?? $order->document_name,
            'template_name' => $data['template_name'] ?? $order->template_name,
            'update_date' => now(),
        ]);

        return $order->fresh() ?? $order;
    }

    public function deleteOrder(ChronologyOrder $order): void
    {
        $order->delete();
    }

    /**
     * @param  list<int>  $ownerIds
     */
    public function saveOwnerOptOuts(Chronology $chronology, array $ownerIds): void
    {
        DB::transaction(function () use ($chronology, $ownerIds): void {
            ChronologyOwnerEmail::query()->where('chronology_id', $chronology->id)->delete();

            foreach ($ownerIds as $ownerId) {
                $owner = Owner::query()->find($ownerId);

                ChronologyOwnerEmail::query()->create([
                    'chronology_id' => $chronology->id,
                    'owner_id' => $ownerId,
                    'owner_email' => $owner?->owner_email,
                    'status' => 1,
                ]);
            }
        });
    }

    /**
     * @return Collection<int, Owner>
     */
    public function previewOwners(Chronology $chronology): Collection
    {
        $subregionIds = $chronology->subregions()->pluck('subregions.id');

        $propertyQuery = Property::query()->notDeleted();

        if ($subregionIds->isNotEmpty()) {
            $propertyQuery->whereIn('subregion_id', $subregionIds);
        }

        if ((int) $chronology->chronologyoption === 2 && $chronology->startdate !== null) {
            $propertyQuery->whereDate('created_date', '>', $chronology->startdate);
        }

        $ownerIds = $propertyQuery
            ->with('owners')
            ->get()
            ->flatMap(fn (Property $property) => $property->owners->pluck('id'))
            ->unique()
            ->values();

        return Owner::query()
            ->notDeleted()
            ->whereIn('id', $ownerIds)
            ->where('emailstatus', 1)
            ->get();
    }

    public function processDueSends(?Carbon $now = null): int
    {
        $now ??= now('America/Los_Angeles');
        $sent = 0;

        $chronologies = Chronology::query()
            ->with(['orders.document', 'orders.template', 'subregions'])
            ->where(function ($query): void {
                $query->whereNull('chronologyoption')
                    ->orWhere('chronologyoption', '!=', 1);
            })
            ->get();

        foreach ($chronologies as $chronology) {
            foreach ($this->previewOwners($chronology) as $owner) {
                if ($this->isOptedOut($chronology, $owner)) {
                    continue;
                }

                $anchor = $this->anchorDateForOwner($chronology, $owner);

                if ($anchor === null) {
                    continue;
                }

                foreach ($chronology->orders as $order) {
                    if ($this->alreadySent($chronology, $order, $owner)) {
                        continue;
                    }

                    if (! $this->isDue($anchor, $order, $now)) {
                        continue;
                    }

                    if ($order->requiresPriorOpen() && ! $this->priorOrderOpened($chronology, $order, $owner)) {
                        continue;
                    }

                    $mail = ChronologyMail::query()->create([
                        'chronology_id' => $chronology->id,
                        'order_id' => $order->id,
                        'chronologyorder' => $order->id,
                        'owner_id' => $owner->id,
                        'created_on' => now(),
                        'is_opened' => '0',
                    ]);

                    $this->sendStep($chronology, $order, $owner, $mail);
                    $sent++;
                }
            }
        }

        return $sent;
    }

    public function sendStep(
        Chronology $chronology,
        ChronologyOrder $order,
        Owner $owner,
        ChronologyMail $mail,
    ): void {
        $document = $order->document_id
            ? DocumentUpload::query()->find($order->document_id)
            : null;
        $template = $order->template_id
            ? EmailTemplate::query()->find($order->template_id)
            : null;

        if ($document?->requiresZohoSign()) {
            $response = $this->zohoSignService->createDocumentFromTemplate((string) $document->signid, [
                'templates' => [
                    'template_id' => $document->signid,
                    'actions' => [
                        [
                            'action_id' => $document->zohoactionid,
                            'recipient_name' => $owner->full_name ?? $owner->first_name,
                            'recipient_email' => $owner->owner_email,
                        ],
                    ],
                ],
            ]);

            $this->zohoSignService->recordSignatureRequest(
                $owner,
                (string) $document->signid,
                $response,
                $chronology->id,
                $order->id,
                $document->document,
            );

            return;
        }

        $subject = $template?->templatesubject ?? ($order->template_name ?? 'Chronology notice');
        $body = $template?->description ?? '';
        $trackingUrl = route('chronology.mail.opened', ['mail' => $mail->id]);
        $body .= '<img src="'.$trackingUrl.'" width="1" height="1" alt="" />';

        $this->emailService->send([
            'to' => (string) $owner->owner_email,
            'subject' => $subject,
            'body' => $body,
            'source' => 'chronology',
            'metadata' => [
                'chronology_id' => $chronology->id,
                'order_id' => $order->id,
                'owner_id' => $owner->id,
                'chronology_mail_id' => $mail->id,
            ],
        ]);
    }

    public function markOpened(ChronologyMail $mail): void
    {
        $mail->markOpened();
    }

    public function pollZohoCompletions(): int
    {
        $completed = 0;

        $pending = HelloSignDetail::query()
            ->where('zoho_sign_status', 0)
            ->whereNotNull('zoho_request_id')
            ->get();

        foreach ($pending as $detail) {
            $request = $this->zohoSignService->getRequest((string) $detail->zoho_request_id);
            $status = strtolower((string) data_get($request, 'requests.request_status', data_get($request, 'status', '')));

            if ($status !== 'completed') {
                continue;
            }

            $detail->update([
                'zoho_sign_status' => 1,
                'is_opened' => 1,
                'update_date' => now(),
            ]);

            ChronologyMail::query()
                ->where('chronology_id', $detail->chronology_id)
                ->where('order_id', $detail->chronologyorder_id)
                ->where('owner_id', $detail->ownerid_id)
                ->update(['is_opened' => '1', 'update_on' => now()]);

            $completed++;
        }

        return $completed;
    }

    /**
     * @param  list<int>  $regionIds
     * @param  list<int>  $subregionIds
     */
    private function syncAudience(Chronology $chronology, array $regionIds, array $subregionIds): void
    {
        $chronology->regions()->sync($regionIds);
        $chronology->subregions()->sync($subregionIds);
    }

    private function isOptedOut(Chronology $chronology, Owner $owner): bool
    {
        return ChronologyOwnerEmail::query()
            ->where('chronology_id', $chronology->id)
            ->where('owner_id', $owner->id)
            ->exists();
    }

    private function alreadySent(Chronology $chronology, ChronologyOrder $order, Owner $owner): bool
    {
        return ChronologyMail::query()
            ->where('chronology_id', $chronology->id)
            ->where('order_id', $order->id)
            ->where('owner_id', $owner->id)
            ->exists();
    }

    private function priorOrderOpened(Chronology $chronology, ChronologyOrder $order, Owner $owner): bool
    {
        $prior = ChronologyOrder::query()
            ->where('chronology_id', $chronology->id)
            ->where('id', '<', $order->id)
            ->orderByDesc('id')
            ->first();

        if ($prior === null) {
            return true;
        }

        return ChronologyMail::query()
            ->where('chronology_id', $chronology->id)
            ->where('order_id', $prior->id)
            ->where('owner_id', $owner->id)
            ->where('is_opened', '1')
            ->exists();
    }

    private function isDue(Carbon $anchor, ChronologyOrder $order, Carbon $now): bool
    {
        $dueDate = $anchor->copy()->addDays((int) $order->day);
        $hour = (int) ($order->time ?? 0);
        $minute = (int) ($order->minute ?? 0);
        $dueAt = $dueDate->copy()->setTime($hour, $minute);

        return $now->greaterThanOrEqualTo($dueAt);
    }

    private function anchorDateForOwner(Chronology $chronology, Owner $owner): ?Carbon
    {
        if ((int) $chronology->chronologyoption === 1) {
            return $chronology->startdate?->copy();
        }

        $property = $owner->properties()
            ->notDeleted()
            ->when(
                $chronology->subregions()->exists(),
                fn ($query) => $query->whereIn('subregion_id', $chronology->subregions()->pluck('subregions.id')),
            )
            ->orderBy('created_date')
            ->first();

        if ($property?->created_date === null) {
            return $chronology->startdate?->copy();
        }

        return $property->created_date->copy();
    }
}
