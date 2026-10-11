<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  foreach (['projects', 'services', 'posts'] as $t) {
   Schema::table($t, function (Blueprint $b) { $b->string('seo_title')->nullable(); $b->string('seo_description', 200)->nullable(); });
  }
 }
 public function down(): void {
  foreach (['projects', 'services', 'posts'] as $t) {
   Schema::table($t, function (Blueprint $b) { $b->dropColumn(['seo_title', 'seo_description']); });
  }
 }
};