<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('projects', function (Blueprint $t) {
   $t->string('client', 150)->nullable();
   $t->string('year', 10)->nullable();
   $t->string('my_role', 150)->nullable();
   $t->text('challenge')->nullable();
   $t->text('solution')->nullable();
   $t->text('result')->nullable();
  });
 }
 public function down(): void {
  Schema::table('projects', function (Blueprint $t) { $t->dropColumn(['client', 'year', 'my_role', 'challenge', 'solution', 'result']); });
 }
};