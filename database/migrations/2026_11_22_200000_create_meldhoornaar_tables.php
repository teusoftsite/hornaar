<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();            // gemeente | bestrijder
            $table->string('name');
            $table->string('cbs_code', 10)->nullable()->unique(); // alleen gemeenten, bijv. GM0777
            $table->string('kvk', 20)->nullable();
            $table->string('vat_number', 30)->nullable();
            $table->string('iban', 40)->nullable();
            $table->string('invoice_email')->nullable();     // factuuradres (gemeente) of afzender (bestrijder)
            $table->string('notify_email')->nullable();      // nieuwe meldingen (gemeente)
            $table->string('address')->nullable();
            $table->unsignedTinyInteger('vat_rate')->default(21); // procent, alleen bestrijder
            $table->decimal('base_lat', 9, 6)->nullable();   // werkplaats bestrijder (startpunt route)
            $table->decimal('base_lng', 9, 6)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role', 30)->default('gemeente')->after('email'); // beheerder | gemeente | bestrijder_eigenaar | bestrijder_medewerker
            $table->boolean('active')->default(true)->after('role');
        });

        Schema::create('municipality_areas', function (Blueprint $table) {
            $table->id();
            $table->string('cbs_code', 10)->index();
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->decimal('min_lat', 9, 6);
            $table->decimal('max_lat', 9, 6);
            $table->decimal('min_lng', 9, 6);
            $table->decimal('max_lng', 9, 6);
            $table->longText('geometry');                    // GeoJSON (Polygon of MultiPolygon, WGS84)
            $table->timestamps();
            $table->index(['min_lat', 'max_lat', 'min_lng', 'max_lng']);
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gemeente_id')->constrained('organisations');
            $table->foreignId('bestrijder_id')->constrained('organisations');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->unsignedInteger('standard_rate_cents');
            $table->unsignedInteger('surcharge_height_cents')->default(0);
            $table->unsignedInteger('surcharge_urgent_cents')->default(0);
            $table->unsignedInteger('callout_cents')->default(0);
            $table->unsignedSmallInteger('payment_days')->default(30);
            $table->timestamps();
        });

        Schema::create('nests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gemeente_id')->nullable()->constrained('organisations'); // null = gemeente doet (nog) niet mee
            $table->string('cbs_code', 10)->nullable()->index();
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->string('address')->nullable();
            $table->string('location_type', 20)->nullable(); // boom | gebouw | struik | grond
            $table->decimal('height_m', 4, 1)->nullable();
            $table->boolean('urgent')->default(false);
            $table->string('status', 30)->default('gemeld')->index();
            $table->foreignId('merged_into_id')->nullable()->constrained('nests');
            $table->timestamps();
            $table->index(['lat', 'lng']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nest_id')->constrained()->cascadeOnDelete();
            $table->string('source', 20)->default('web');    // web | waarneming
            $table->string('photo_path')->nullable();
            $table->text('note')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('token', 40)->unique();
            $table->string('ip_hash', 64)->nullable();
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('planned_on')->nullable();
            $table->unsignedSmallInteger('route_order')->nullable();
            $table->string('status', 20)->default('open')->index(); // open | ingepland | afgerond | geannuleerd
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->uuid('client_uuid')->unique();           // idempotent bij offline opnieuw versturen
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->string('agent')->nullable();             // middel
            $table->string('admission_number', 30)->nullable(); // toelatingsnummer Ctgb
            $table->string('quantity', 50)->nullable();
            $table->json('surcharges')->nullable();          // ["hoogwerker","urgent"]
            $table->string('result', 20);                    // bestreden | geen_toegang | geen_nest
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bestrijder_id')->constrained('organisations');
            $table->foreignId('gemeente_id')->constrained('organisations');
            $table->foreignId('contract_id')->constrained();
            $table->string('period', 7);                     // JJJJ-MM
            $table->string('number', 20)->nullable();        // JJJJ-NNNN, toegekend bij versturen
            $table->string('status', 20)->default('concept'); // concept | verstuurd | betaald | te_laat | credit
            $table->foreignId('credits_invoice_id')->nullable()->constrained('invoices');
            $table->integer('subtotal_cents')->default(0);
            $table->integer('vat_cents')->default(0);
            $table->integer('total_cents')->default(0);
            $table->unsignedTinyInteger('vat_rate')->default(21);
            $table->dateTime('sent_at')->nullable();
            $table->date('due_on')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->unsignedTinyInteger('reminders_sent')->default(0);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->unique(['bestrijder_id', 'number']);
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->cascadeOnDelete(); // null = nog niet gefactureerd
            $table->foreignId('contract_id')->constrained();
            $table->foreignId('work_order_id')->nullable()->unique()->constrained();
            $table->string('description');
            $table->integer('amount_cents');
            $table->timestamps();
        });

        Schema::create('status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('from', 30)->nullable();
            $table->string('to', 30);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['status_events', 'invoice_lines', 'invoices', 'work_orders', 'assignments', 'reports', 'nests', 'contracts', 'municipality_areas'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organisation_id');
            $table->dropColumn(['role', 'active']);
        });
        Schema::dropIfExists('organisations');
    }
};
