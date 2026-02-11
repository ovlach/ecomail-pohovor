<?php

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::createFunctionOrReplace(
            name: 'update_contacts_ts_name',
            parameters: [],
            return: 'trigger',
            language: 'plpgsql',
            body: <<<'SQL'
            BEGIN
                IF TG_OP = 'INSERT'
                    OR NEW.first_name IS DISTINCT FROM OLD.first_name
                    OR NEW.last_name  IS DISTINCT FROM OLD.last_name THEN
                  NEW.ts_name =
                      to_tsvector('simple',
                          coalesce(NEW.first_name, '') || ' ' || coalesce(NEW.last_name, '')
                      );
                END IF;
                RETURN NEW;
            END;
        SQL);

        Schema::createExtensionIfNotExists('pg_trgm');

        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 254)->unique();
            $table->tsvector('ts_name');
            $table->trigger('ts_name_contacts_build_vector', 'update_contacts_ts_name()', 'BEFORE INSERT OR UPDATE')->forEachRow(); // mising indexes (GIN!!! etc.)
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_contacts_email_trgm ON contacts USING GIN (email gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_contacts_ts_name ON contacts USING GIN (ts_name);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropFunctionIfExists('update_contacts_ts_name');
    }
};
