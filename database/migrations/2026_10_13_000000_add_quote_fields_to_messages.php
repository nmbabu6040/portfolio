<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('messages', function (Blueprint $t) {
   $t->string('kind')->default('contact');
   $t->string('service')->nullable();
   $t->string('budget')->nullable();
   $t->string('deadline')->nullable();
  });
 }
 public function down(): void {
  Schema::table('messages', function (Blueprint $t) { $t->dropColumn(['kind', 'service', 'budget', 'deadline']); });
 }
};