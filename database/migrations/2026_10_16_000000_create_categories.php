<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('categories', function (Blueprint $t) {
   $t->id(); $t->string('title'); $t->string('slug')->unique(); $t->integer('sort')->default(0); $t->timestamps();
  });
  Schema::table('posts', function (Blueprint $t) { $t->string('category')->nullable()->index(); });
 }
 public function down(): void {
  Schema::table('posts', function (Blueprint $t) { $t->dropColumn('category'); });
  Schema::dropIfExists('categories');
 }
};