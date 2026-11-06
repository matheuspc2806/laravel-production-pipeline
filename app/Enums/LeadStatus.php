<?php

namespace App\Enums;

/**
 * Estágios do pipeline de vendas:
 * New → Qualified → Contacted → Replied → Call booked → Proposal → Won / Lost
 */
enum LeadStatus: string
{
    case New = 'new';
    case Qualified = 'qualified';
    case Contacted = 'contacted';
    case Replied = 'replied';
    case CallBooked = 'call_booked';
    case Proposal = 'proposal';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Qualified => 'Qualified',
            self::Contacted => 'Contacted',
            self::Replied => 'Replied',
            self::CallBooked => 'Call booked',
            self::Proposal => 'Proposal',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Qualified => 'info',
            self::Contacted, self::Replied => 'warning',
            self::CallBooked, self::Proposal => 'primary',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }
}
