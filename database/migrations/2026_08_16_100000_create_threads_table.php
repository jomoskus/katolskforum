<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            // Not unique: URLs are self-healing ("{slug}-{id}") and resolve
            // by the trailing id, so duplicate slugs are harmless.
            $table->string('slug');
            $table->string('kind', 20);
            $table->text('body');
            $table->string('url', 2048)->nullable();
            $table->timestamps();

            // The front page sorts threads by last activity (= updated_at,
            // because posts touch their thread).
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
