<?php

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
        Schema::create('on_this_day_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('lang', 10)->default('zh'); // supported: zh, en, ...
            $table->integer('year')->nullable();
            $table->integer('month');
            $table->integer('day');
            $table->enum('type', ['selected', 'births','deaths', 'events', 'holidays']); // supported: No --all-- /selected/events/births/deaths/holidays
            $table->text('text');
            $table->json('payload');
            $table->dateTime('event_datetime')->nullable();

            $table->index(['year', 'lang', 'type', 'month', 'day']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('on_this_day_snapshots');
    }
};
