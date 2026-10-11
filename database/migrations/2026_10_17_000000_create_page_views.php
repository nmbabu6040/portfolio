<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('page_views', function (Blueprint $t) {
   $t->id();
   $t->date('day')->index();
   $t->string('path', 190);
   $t->string('visitor', 32);
   $t->string('referrer', 100)->nullable();
   $t->string('device', 10);
   $t->timestamp('created_at')->nullable();
   $t->index(['day', 'path']);
  });
 }
 public function down(): void { Schema::dropIfExists('page_views'); }
};