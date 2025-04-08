<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// In the migration file
public function up()
{
    Schema::create('notifications', function (Blueprint $table) {
        $table->id(); // ID of the notification
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // id of the user who created the notification
        $table->string('type'); // e.g., 'message', 'mention' // type of the notification
        $table->morphs('notifiable'); // Polymorphic relation // relation to the notifiable model
        $table->text('data'); // data of the notification
        $table->timestamp('read_at')->nullable(); // timestamp of when the notification was read
        $table->timestamps(); // timestamps of the notification
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_tabel');
    }
};
