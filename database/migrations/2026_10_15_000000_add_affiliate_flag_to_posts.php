<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('posts', function (Blueprint $t) { $t->boolean('has_affiliate')->default(false); });
 }
 public function down(): void {
  Schema::table('posts', function (Blueprint $t) { $t->dropColumn('has_affiliate'); });
 }
};