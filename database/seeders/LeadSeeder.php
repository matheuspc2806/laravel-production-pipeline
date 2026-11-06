<?php

namespace Database\Seeders;

use App\Enums\LeadStatus;
use App\Enums\Niche;
use App\Models\Lead;
use Illuminate\Database\Seeder;

/**
 * Prospects REAIS (odontologia, Columbus/OH) coletados de diretórios PÚBLICOS
 * (WebMD, Superpages, MapQuest, Vitals).
 *
 * IMPORTANTE: nenhum lead aqui carrega afirmação sobre stack ou segurança.
 * Os campos `website`, `stack`, `signals` e `score` ficam vazios de propósito —
 * você os preenche lead a lead durante o "Check de 60s" (Wappalyzer + PageSpeed),
 * que é a forma legítima de qualificar. Idempotente (updateOrCreate por company).
 */
class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $leads = [
            ['company' => 'Gentle Family Dental Care', 'source' => 'Superpages', 'notes' => 'Family & cosmetic practice.'],
            ['company' => 'Tooth Family Dentistry', 'contact_name' => 'Dr. Mehrdad Safavian, DDS', 'source' => 'WebMD', 'notes' => '65 E State St, Columbus, OH 43215 (downtown).'],
            ['company' => 'Atlas Family Dentistry', 'phone' => '+1-614-929-2400', 'source' => 'newpatientsinc', 'notes' => 'Solo practice on Old Hamilton Rd.'],
            ['company' => 'Columbus Family Dental Care', 'contact_name' => 'Dr. David Dixon', 'source' => 'Vitals', 'notes' => 'Independent practice since 1989.'],
            ['company' => 'Polaris Family Dental', 'source' => 'WebMD', 'notes' => '8949 Antares Ave, Columbus, OH.'],
            ['company' => 'Fulks Family Dental', 'source' => 'WebMD', 'notes' => '2607 E Main St, Columbus, OH.'],
            ['company' => 'Southwest Family Dentistry', 'source' => 'WebMD', 'notes' => '699 Harrisburg Pike, Columbus, OH.'],
            ['company' => 'Morse Road Family Dental Group', 'source' => 'WebMD', 'notes' => '1100 Morse Rd, Columbus, OH.'],
            ['company' => 'Gerda Family Dental', 'source' => 'MapQuest', 'notes' => 'Columbus, OH.'],
            ['company' => 'Northside Family Dental', 'source' => 'MapQuest', 'notes' => 'Columbus, OH.'],
            ['company' => 'Murray Hill Dental', 'phone' => '+1-614-878-1188', 'source' => 'Directory', 'notes' => 'Family & cosmetic, ~3 locations. Confirme o site oficial.'],
            ['company' => 'Lin Douglas, DDS', 'source' => 'MapQuest', 'notes' => 'Pediatric dental practice, Columbus, OH.'],
            ['company' => 'Hines Little Smiles', 'source' => 'Job listing', 'notes' => 'Pediatric practice, Columbus, OH.'],
        ];

        foreach ($leads as $data) {
            Lead::updateOrCreate(
                ['company' => $data['company']],
                array_merge([
                    'niche' => Niche::Dental,
                    'status' => LeadStatus::New,
                    'city' => 'Columbus',
                    'state' => 'OH',
                    'country' => 'US',
                    'stack_confirmed' => false,
                ], $data),
            );
        }
    }
}
