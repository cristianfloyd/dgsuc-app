<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Override;

class Documentation extends Model
{
    use HasFactory;

    protected $table = 'documentacion';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'section',
        'order',
        'is_published',
    ];

    public static function getMarkdownContent(string $file): string
    {
        $path = resource_path("docs/{$file}.md");

        if (!file_exists($path)) {
            return '';
        }

        return file_get_contents($path);
    }

    public static function getSections(): array
    {
        return [
            'general' => 'Documentación General',
            'liquidaciones' => 'Panel de Liquidaciones',
            'embargos' => 'Panel de Embargos',
            'reportes' => 'Panel de Reportes',
            'admin' => 'Panel Administrativo',
        ];
    }

    #[Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($doc): void {
            if (empty($doc->slug)) {
                $doc->slug = Str::slug($doc->title);
            }
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'order' => 'integer',
        ];
    }
}
