<?php

namespace App\Services\Documents;

use App\Models\DocumentUpload;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * @param  array{name: string, ownerspecific?: string|null, type?: int|null, signid?: string|null, zohoactionid?: string|null, roletitle?: string|null, region_ids?: list<int>, subregion_ids?: list<int>, owner_ids?: list<int>}  $attributes
     */
    public function create(array $attributes, ?UploadedFile $file = null): DocumentUpload
    {
        $path = null;

        if ($file !== null) {
            $path = $file->storeAs(
                'documents',
                Str::slug($attributes['name']).'-'.uniqid().'.'.$file->getClientOriginalExtension(),
                'local',
            );
        }

        $document = DocumentUpload::query()->create([
            'name' => $attributes['name'],
            'ownerspecific' => $attributes['ownerspecific'] ?? 'no',
            'document' => $path ?? ($attributes['document'] ?? null),
            'type' => $attributes['type'] ?? 0,
            'signid' => $attributes['signid'] ?? null,
            'zohoactionid' => $attributes['zohoactionid'] ?? null,
            'roletitle' => $attributes['roletitle'] ?? null,
            'createdate' => now(),
        ]);

        $document->regions()->sync($attributes['region_ids'] ?? []);
        $document->subregions()->sync($attributes['subregion_ids'] ?? []);
        $document->owners()->sync($attributes['owner_ids'] ?? []);

        return $document->load(['regions', 'subregions', 'owners']);
    }

    /**
     * @param  array{name?: string, ownerspecific?: string|null, type?: int|null, signid?: string|null, zohoactionid?: string|null, roletitle?: string|null, region_ids?: list<int>, subregion_ids?: list<int>, owner_ids?: list<int>}  $attributes
     */
    public function update(DocumentUpload $document, array $attributes): DocumentUpload
    {
        $document->update(collect($attributes)->except(['region_ids', 'subregion_ids', 'owner_ids'])->all());

        if (array_key_exists('region_ids', $attributes)) {
            $document->regions()->sync($attributes['region_ids'] ?? []);
        }

        if (array_key_exists('subregion_ids', $attributes)) {
            $document->subregions()->sync($attributes['subregion_ids'] ?? []);
        }

        if (array_key_exists('owner_ids', $attributes)) {
            $document->owners()->sync($attributes['owner_ids'] ?? []);
        }

        return $document->fresh(['regions', 'subregions', 'owners']) ?? $document;
    }

    public function delete(DocumentUpload $document): void
    {
        if (filled($document->document) && Storage::disk('local')->exists($document->document)) {
            Storage::disk('local')->delete($document->document);
        }

        $document->regions()->detach();
        $document->subregions()->detach();
        $document->owners()->detach();
        $document->delete();
    }

    /**
     * @return Collection<int, DocumentUpload>
     */
    public function list(): Collection
    {
        return DocumentUpload::query()->orderBy('name')->get();
    }
}
