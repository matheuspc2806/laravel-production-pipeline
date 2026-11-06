<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela central do CRM: prospects de manutenção Laravel/PHP (mercado EUA).
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('company');
            $table->string('niche')->index();                   // App\Enums\Niche
            $table->string('status')->default('new')->index();  // App\Enums\LeadStatus
            $table->unsignedTinyInteger('score')->nullable();    // 1..5 (Check de 60s)

            // Localização (foco EUA)
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('country', 2)->default('US');

            // Presença web — preenchido durante a qualificação (Wappalyzer / PageSpeed)
            $table->string('website')->nullable();
            $table->string('stack')->nullable();                 // ex.: "Laravel", "PHP"
            $table->boolean('stack_confirmed')->default(false);
            $table->text('signals')->nullable();                 // sinais públicos de site neglected

            // Contato
            $table->string('contact_name')->nullable();
            $table->string('contact_role')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Pipeline
            $table->string('source')->nullable();                // diretório/fonte pública
            $table->timestamp('last_contacted_at')->nullable();
            $table->string('next_action')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
