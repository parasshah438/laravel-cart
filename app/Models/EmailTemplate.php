<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Fetch a template by key and replace {{variable}} placeholders.
     * Returns ['subject' => '...', 'html' => '...'] or null if not found / inactive.
     */
    public static function render(string $key, array $vars = []): ?array
    {
        $tpl = static::where('key', $key)->where('is_active', true)->first();
        if (! $tpl) {
            return null;
        }

        $search  = array_map(fn ($v) => '{{' . $v . '}}', array_keys($vars));
        $replace = array_values($vars);

        return [
            'subject' => str_replace($search, $replace, $tpl->subject),
            'html'    => str_replace($search, $replace, $tpl->body),
        ];
    }
}
