<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('activity_logs', function (Blueprint $t) {
   $t->id();
   $t->unsignedBigInteger('user_id')->nullable();
   $t->string('user_name', 100);
   $t->string('action', 20)->index();
   $t->string('subject', 60)->nullable();
   $t->unsignedBigInteger('subject_id')->nullable();
   $t->string('summary', 250);
   $t->string('ip', 45)->nullable();
   $t->timestamp('created_at')->nullable()->index();
  });
  Schema::table('posts', function (Blueprint $t) { $t->text('excerpt')->nullable()->change(); });
 }
 public function down(): void { Schema::dropIfExists('activity_logs'); }
};