<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('messages', function (Blueprint $t) {
   $t->string('stage', 20)->default('new')->index();
   $t->unsignedBigInteger('deal_value')->nullable();
   $t->date('follow_up')->nullable();
   $t->text('notes')->nullable();
   $t->string('source', 60)->nullable()->index();
   $t->string('landing', 190)->nullable();
   $t->string('campaign', 100)->nullable();
   $t->string('referrer', 100)->nullable();
  });
  Schema::create('cta_clicks', function (Blueprint $t) {
   $t->id();
   $t->string('name', 20);
   $t->string('path', 190);
   $t->date('day')->index();
   $t->timestamp('created_at')->nullable();
   $t->index(['day', 'name']);
  });
 }
 public function down(): void {
  Schema::dropIfExists('cta_clicks');
  Schema::table('messages', function (Blueprint $t) {
   $t->dropColumn(['stage', 'deal_value', 'follow_up', 'notes', 'source', 'landing', 'campaign', 'referrer']);
  });
 }
};