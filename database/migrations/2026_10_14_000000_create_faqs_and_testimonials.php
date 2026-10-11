<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('faqs', function (Blueprint $t) {
   $t->id(); $t->string('question'); $t->text('answer'); $t->string('service')->nullable();
   $t->integer('sort')->default(0); $t->timestamps();
  });
  Schema::create('testimonials', function (Blueprint $t) {
   $t->id(); $t->string('name'); $t->string('role')->nullable(); $t->text('quote'); $t->string('photo')->nullable();
   $t->string('url')->nullable(); $t->boolean('permission')->default(false); $t->boolean('visible')->default(true);
   $t->integer('sort')->default(0); $t->timestamps();
  });
 }
 public function down(): void { Schema::dropIfExists('testimonials'); Schema::dropIfExists('faqs'); }
};