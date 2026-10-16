<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => env('ADMIN_EMAIL', 'admin@example.com')], ['name' => 'Admin', 'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!'))]);

        User::where('email', env('ADMIN_EMAIL', 'admin@example.com'))->update(['role' => 'super_admin']);

        $c = config('portfolio');
        $now = now();
        $put = function (string $t, array $rows) use ($now) {
            if (DB::table($t)->count() || !$rows) return;
            DB::table($t)->insert(collect($rows)->values()->map(fn($r, $i) => $r + ['sort' => $i, 'created_at' => $now, 'updated_at' => $now])->all());
        };
        $put('experiences', array_map(fn($e) => ['period' => $e[0], 'title' => $e[1], 'org' => $e[2], 'areas' => implode(', ', $e[3])], $c['experience']));
        $put('technologies', array_merge(
            array_map(fn($s) => ['name' => $s[0], 'category' => $s[1], 'description' => $s[2], 'level' => null, 'visible' => 1], $c['stack']),
            array_map(fn($s) => ['name' => $s[0], 'category' => $s[1], 'description' => $s[2], 'level' => null, 'visible' => 0], $c['graphics'])
        ));
        $put('skills', array_map(fn($g, $i) => ['title' => $g, 'items' => implode(', ', $i)], array_keys($c['skills']), $c['skills']));
        $put('services', array_map(fn($s) => ['title' => $s[0], 'slug' => \Illuminate\Support\Str::slug($s[0]), 'description' => $s[1], 'details' => config('service_details')[$s[0]] ?? null], $c['services']));
        $put('workflows', array_map(fn($s) => ['title' => $s[0], 'description' => $s[1]], $c['workflow']));
        $put('stats', array_map(fn($s) => ['value' => $s[0], 'label' => $s[1]], $c['stats']));
        $put('reasons', array_map(fn($s) => ['title' => $s], $c['why']));
    }
}
