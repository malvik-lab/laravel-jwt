<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAuthTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->json('roles')->nullable();
            $table->json('permissions')->nullable();
            $table->uuid('at_jti')->index()->unique();
            $table->integer('at_exp')->nullable();
            $table->boolean('at_revoked')->default(false);
            $table->uuid('rt_jti')->index()->unique();
            $table->integer('rt_exp')->nullable();
            $table->boolean('rt_revoked')->default(false);
            $table->softDeletes();
        });

        DB::unprepared('
            CREATE TRIGGER token_unique BEFORE INSERT ON auth_tokens
            FOR EACH ROW
            BEGIN
                DECLARE existingToken1 INT;
                DECLARE existingToken2 INT;

                SELECT COUNT(*) INTO existingToken1
                FROM auth_tokens
                WHERE at_jti = NEW.at_jti OR rt_jti = NEW.at_jti;

                SELECT COUNT(*) INTO existingToken2
                FROM auth_tokens
                WHERE at_jti = NEW.rt_jti OR rt_jti = NEW.rt_jti;

                IF existingToken1 > 0 THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "at_jti is not unique";
                END IF;

                IF existingToken2 > 0 THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "rt_jti is not unique";
                END IF;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_tokens');
    }
}
