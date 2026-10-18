<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('documents', function (Blueprint $t) {
   $t->id();
   $t->string('type', 12)->index();
   $t->string('number', 20)->unique();
   $t->unsignedBigInteger('lead_id')->nullable()->index();
   $t->unsignedBigInteger('from_id')->nullable();
   $t->string('client_name', 150);
   $t->string('client_email', 150)->nullable();
   $t->string('client_phone', 40)->nullable();
   $t->text('client_address')->nullable();
   $t->date('issue_date');
   $t->date('due_date')->nullable();
   $t->string('currency', 8)->default('BDT');
   $t->decimal('tax_percent', 5, 2)->default(0);
   $t->decimal('discount', 14, 2)->default(0);
   $t->text('notes')->nullable();
   $t->text('terms')->nullable();
   $t->string('status', 12)->default('draft');
   $t->unsignedBigInteger('created_by')->nullable();
   $t->timestamps();
  });
  Schema::create('document_items', function (Blueprint $t) {
   $t->id();
   $t->unsignedBigInteger('document_id')->index();
   $t->string('description', 300);
   $t->decimal('qty', 12, 2)->default(1);
   $t->decimal('price', 14, 2)->default(0);
   $t->integer('sort')->default(0);
  });
 }
 public function down(): void {
  Schema::dropIfExists('document_items');
  Schema::dropIfExists('documents');
 }
};