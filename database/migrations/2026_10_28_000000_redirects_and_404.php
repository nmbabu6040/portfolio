<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('redirects', function (Blueprint $t) {
   $t->id();
   $t->string('from_path', 190)->unique();
   $t->string('to_url', 400);
   $t->unsignedSmallInteger('code')->default(301);
   $t->unsignedInteger('hits')->default(0);
   $t->timestamp('last_hit_at')->nullable();
   $t->boolean('auto')->default(false);
   $t->timestamps();
  });
  Schema::create('not_found_log', function (Blueprint $t) {
   $t->id();
   $t->string('path', 190)->unique();
   $t->unsignedInteger('hits')->default(1);
   $t->string('referrer', 100)->nullable();
   $t->boolean('is_bot')->default(false)->index();
   $t->boolean('ignored')->default(false);
   $t->timestamp('first_seen')->nullable();
   $t->timestamp('last_seen')->nullable()->index();
  });
 }
 public function down(): void {
  Schema::dropIfExists('not_found_log');
  Schema::dropIfExists('redirects');
 }
};