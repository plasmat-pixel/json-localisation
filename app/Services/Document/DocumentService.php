<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Models\Project;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class DocumentService
{
    private Project $project;
    private Document $document;
    public function add(array $documents): DocumentService
    {

        $this->project->documents()->createMany($documents);

        return $this;
    }

    public function setProject(Project | int $project): DocumentService
    {
        $this->project = $project instanceof Project
        ? $project
        : Project::query()->findOrFail($project);
        return $this;
    }

    public function list(): Collection
    {
        return $this->project->documents()->get();
    }

    public function importTranslation(int $lang, array $translations)
    {
        $translatedData = [];

        $existsTranslation = Translation::query()
            ->where('language_id', $lang)
            ->where('document_id', $this->document->id)
            ->first();

        $sourceData = is_null($existsTranslation)
        ? $this->document->data
        : $existsTranslation->data;

        foreach ($sourceData as $item) {
            $targetItem = Arr::first($translations, function ($el) use ($item) {
                return $el['key'] === $item['key'];
            });

            if (is_null($targetItem)) {
                if (is_null($existsTranslation)) {
                    $item['value'] = '';
                }
            } else {
                $item['value'] = $targetItem['value'];
            }
            $translatedData[] = $item;
        }
        Translation::query()->updateOrCreate([
            'language_id' => $lang,
            'document_id' => $this->document->id,
        ], [
            'data' => $translatedData,
        ]
        );

        $this->updateProgressTranslation();
    }

    private function updateProgressTranslation()
    {
        $total = $this->document->totalSegments();
        $translated = $this->document->translatedSegmentsCount();
        $progress = ($translated / $total) * 100;
        $this->document->update([
            'progress' => $progress,
        ]);
    }

    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    public function getTranslations(int $lang)
    {
        $data = [
            'name' => $this->document->name,
            'data' => [],
        ];

        $sourceData = Arr::map($this->document->data, function ($el) {
            return [
                'key' => $el['key'],
                'original' => $el['value'],
            ];
        });
        $translations = Translation::query()
            ->where('language_id', $lang)
            ->where('document_id', $this->document->id)
            ->first();

        if (!is_null($translations)) {
            foreach ($sourceData as $item) {
                $targetItem = Arr::first($translations->data, function ($el) use ($item) {
                    return $el['key'] === $item['key'];
                });

                if (!is_null($targetItem)) {
                    $item['translation'] = $targetItem['value'];
                }

                $data['data'][] = $item;
            }
        } else {
            $data['data'] = $sourceData;
        }

        return $data;
    }
}
