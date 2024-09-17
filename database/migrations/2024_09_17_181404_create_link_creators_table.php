<?php declare(strict_types=1);

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
        Schema::create('link_creators', function (Blueprint $table) {
            $table->comment('таблиця авторів які хотіли створити short_url по одинаковому original_url');
            $table->id();
            $table->foreignId('link_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('author_name')->comment('автор який хотів створити short_url по одинаковому original_url');
            $table->timestamps();

            $table->unique(['link_id', 'author_name'], 'link_creators_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_creators');
    }
};
