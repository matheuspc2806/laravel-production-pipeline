<?php

namespace App\Enums;

/**
 * Nichos (todos fora de TI) que o kit de prospecção cobre.
 *
 * Mantido como enum puro (sem contratos do Filament) para o repositório
 * continuar instalável/CI-verde antes do Filament ser provisionado.
 * Os helpers label()/color() são usados pelo painel após a instalação.
 */
enum Niche: string
{
    case Dental = 'dental';
    case Legal = 'legal';
    case Restaurants = 'restaurants';
    case RealEstate = 'real_estate';
    case Fitness = 'fitness';
    case Nonprofit = 'nonprofit';

    public function label(): string
    {
        return match ($this) {
            self::Dental => 'Dentistry & Medical',
            self::Legal => 'Law Firms',
            self::Restaurants => 'Restaurants',
            self::RealEstate => 'Real Estate',
            self::Fitness => 'Fitness Studios',
            self::Nonprofit => 'Nonprofits',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Dental => 'info',
            self::Legal => 'warning',
            self::Restaurants => 'danger',
            self::RealEstate => 'success',
            self::Fitness => 'primary',
            self::Nonprofit => 'gray',
        };
    }
}
