<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function (Blueprint $t) {
   $t->string('role', 20)->default('subscriber');
   $t->boolean('active')->default(true);
  });
  Schema::table('posts', function (Blueprint $t) { $t->unsignedBigInteger('user_id')->nullable()->index(); });
  $first = DB::table('users')->min('id');
  if ($first) DB::table('users')->where('id', $first)->update(['role' => 'super_admin']);
 }
 public function down(): void {
  Schema::table('posts', function (Blueprint $t) { $t->dropColumn('user_id'); });
  Schema::table('users', function (Blueprint $t) { $t->dropColumn(['role', 'active']); });
 }
};