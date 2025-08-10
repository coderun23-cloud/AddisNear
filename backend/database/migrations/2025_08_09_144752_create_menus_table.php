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
          Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->onDelete('cascade'); // foreign key to places
            $table->string('name'); // e.g., "Breakfast", "Drinks", "Lunch"
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2); // price of menu item
            $table->string('image')->nullable(); // optional image for menu item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
