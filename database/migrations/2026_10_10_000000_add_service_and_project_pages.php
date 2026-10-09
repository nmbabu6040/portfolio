<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;
return new class extends Migration {
 public function up(): void {
  Schema::table('services', function (Blueprint $t) { $t->string('slug')->nullable()->unique(); $t->string('icon')->nullable(); $t->text('details')->nullable(); });
  Schema::table('projects', function (Blueprint $t) { $t->text('details')->nullable(); });
  $d = config('service_details');
  foreach (DB::table('services')->get() as $s) {
   $slug = Str::slug($s->title);
   if (DB::table('services')->where('slug', $slug)->exists()) $slug .= '-' . $s->id;
   DB::table('services')->where('id', $s->id)->update(['slug' => $slug, 'details' => $d[$s->title] ?? null]);
  }
 }
 public function down(): void {
  Schema::table('services', function (Blueprint $t) { $t->dropColumn(['slug', 'icon', 'details']); });
  Schema::table('projects', function (Blueprint $t) { $t->dropColumn('details'); });
 }
};