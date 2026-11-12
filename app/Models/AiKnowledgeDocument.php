<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKnowledgeDocument extends Model
{
    protected $table = 'ai_knowledge_documents';

    protected $fillable = [
        'title',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'openai_file_id',
        'status',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_by' => 'integer',
    ];
}
