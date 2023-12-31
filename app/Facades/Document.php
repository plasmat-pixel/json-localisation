<?php

namespace App\Facades;

use App\Models\Document as ModelsDocument;
use App\Models\Project;
use Illuminate\Support\Facades\Facade;

class Document extends Facade
{
    /**
     * @method static \App\Services\Document\DocumentService add(array $document)
     * @method static \Illuminate\Database\Eloquent\Collection list()
     * @method static \App\Services\Document\DocumentService setProject(Project|int $project)
     * @method static \App\Services\Document\DocumentService setDocument(ModelsDocument $document)
     * @see \App\Services\Document\DocumentService
     */

    protected static function getFacadeAccessor(): string
    {
        return 'documents';
    }
}
