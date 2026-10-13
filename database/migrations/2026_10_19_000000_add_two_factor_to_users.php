<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function (Blueprint $t) {
   $t->text('two_factor_secret')->nullable();
   $t->text('two_factor_recovery')->nullable();
   $t->boolean('two_factor_enabled')->default(false);
   $t->unsignedBigInteger('two_factor_last')->nullable();
  });
 }
 public function down(): void {
  Schema::table('users', function (Blueprint $t) {
   $t->dropColumn(['two_factor_secret', 'two_factor_recovery', 'two_factor_enabled', 'two_factor_last']);
  });
 }
};