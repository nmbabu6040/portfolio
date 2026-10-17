<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('comments', function (Blueprint $t) {
   $t->id();
   $t->unsignedBigInteger('post_id')->index();
   $t->unsignedBigInteger('parent_id')->nullable()->index();
   $t->string('name', 80);
   $t->string('email', 150);
   $t->text('body');
   $t->string('status', 10)->default('pending')->index();
   $t->boolean('is_admin')->default(false);
   $t->unsignedBigInteger('user_id')->nullable();
   $t->string('ip_hash', 64)->nullable()->index();
   $t->timestamps();
  });
  Schema::create('post_likes', function (Blueprint $t) {
   $t->id();
   $t->unsignedBigInteger('post_id');
   $t->string('visitor', 64);
   $t->timestamp('created_at')->nullable();
   $t->unique(['post_id', 'visitor']);
  });
  Schema::table('posts', function (Blueprint $t) {
   $t->unsignedInteger('share_count')->default(0);
   $t->boolean('comments_open')->default(true);
  });
 }
 public function down(): void {
  Schema::table('posts', function (Blueprint $t) { $t->dropColumn(['share_count', 'comments_open']); });
  Schema::dropIfExists('post_likes');
  Schema::dropIfExists('comments');
 }
};