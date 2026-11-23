<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $t) {
            $t->string('phone', 30)->nullable()->after('notify_email');
            $t->boolean('accepts_leads')->default(false)->after('vat_rate');
            $t->unsignedSmallInteger('service_radius_km')->default(25)->after('accepts_leads');
        });

        // Facturen van MeldHoornaar aan bestrijders voor particuliere leads.
        Schema::create('lead_invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bestrijder_id')->constrained('organisations');
            $t->string('period', 7);
            $t->string('number', 20)->nullable()->unique();
            $t->string('status', 20)->default('concept'); // concept, verstuurd, betaald
            $t->unsignedInteger('lead_count')->default(0);
            $t->integer('subtotal_cents')->default(0);
            $t->unsignedTinyInteger('vat_rate')->default(21);
            $t->integer('vat_cents')->default(0);
            $t->integer('total_cents')->default(0);
            $t->timestamp('sent_at')->nullable();
            $t->date('due_on')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->string('pdf_path')->nullable();
            $t->timestamps();
        });

        Schema::create('leads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('nest_id')->constrained();
            $t->foreignId('report_id')->constrained();
            $t->string('name', 120);
            $t->string('phone', 30);
            $t->string('email', 190)->nullable();
            $t->string('status', 20)->default('zoeken'); // zoeken, aangeboden, geaccepteerd, afgerond, geen_bestrijder, geannuleerd
            $t->foreignId('offered_to_id')->nullable()->constrained('organisations');
            $t->timestamp('offered_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->json('declined_ids')->nullable();
            $t->foreignId('bestrijder_id')->nullable()->constrained('organisations');
            $t->timestamp('accepted_at')->nullable();
            $t->unsignedInteger('fee_cents')->default(0);
            $t->foreignId('lead_invoice_id')->nullable()->constrained('lead_invoices');
            $t->string('result', 20)->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->timestamps();
            $t->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_invoices');
        Schema::table('organisations', fn (Blueprint $t) => $t->dropColumn(['phone', 'accepts_leads', 'service_radius_km']));
    }
};
