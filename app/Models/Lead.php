<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\Niche;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company',
    'niche',
    'status',
    'score',
    'city',
    'state',
    'country',
    'website',
    'stack',
    'stack_confirmed',
    'signals',
    'contact_name',
    'contact_role',
    'email',
    'phone',
    'source',
    'last_contacted_at',
    'next_action',
    'notes',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'niche' => Niche::class,
            'status' => LeadStatus::class,
            'stack_confirmed' => 'boolean',
            'score' => 'integer',
            'last_contacted_at' => 'datetime',
        ];
    }
}
