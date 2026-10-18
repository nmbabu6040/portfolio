<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('project_media', function (Blueprint $t) {
   $t->id();
   $t->unsignedBigInteger('project_id')->index();
   $t->string('type', 8);
   $t->string('path')->nullable();
   $t->string('url', 300)->nullable();
   $t->string('caption', 150)->nullable();
   $t->integer('sort')->default(0);
  });
 }
 public function down(): void { Schema::dropIfExists('project_media'); }
};