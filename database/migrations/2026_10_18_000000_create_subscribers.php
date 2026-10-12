<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('subscribers', function (Blueprint $t) {
   $t->id();
   $t->string('email', 150)->unique();
   $t->string('token', 40);
   $t->string('status', 20)->default('active');
   $t->string('source', 150)->nullable();
   $t->timestamps();
  });
 }
 public function down(): void { Schema::dropIfExists('subscribers'); }
};