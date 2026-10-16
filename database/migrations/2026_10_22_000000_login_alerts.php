<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function (Blueprint $t) { $t->boolean('login_alerts')->default(true); });
  Schema::create('login_devices', function (Blueprint $t) {
   $t->id();
   $t->unsignedBigInteger('user_id')->index();
   $t->string('token_hash', 64)->index();
   $t->string('user_agent', 255)->nullable();
   $t->string('ip', 45)->nullable();
   $t->timestamp('created_at')->nullable();
   $t->timestamp('last_seen_at')->nullable();
  });
 }
 public function down(): void {
  Schema::dropIfExists('login_devices');
  Schema::table('users', function (Blueprint $t) { $t->dropColumn('login_alerts'); });
 }
};