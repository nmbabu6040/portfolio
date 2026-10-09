<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function std(string $name, callable $cols): void
    {
        Schema::create($name, function (Blueprint $t) use ($cols) {
            $t->id();
            $cols($t);
            $t->integer('sort')->default(0);
            $t->timestamps();
        });
    }
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->string('name')->primary();
            $t->text('value')->nullable();
        });
        Schema::create('messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('subject');
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });
        $this->std('projects', function ($t) {
            $t->string('title');
            $t->string('category')->default('Web Development');
            $t->text('description')->nullable();
            $t->string('tech')->nullable();
            $t->text('features')->nullable();
            $t->string('demo')->nullable();
            $t->string('code')->nullable();
            $t->string('image')->nullable();
        });
        $this->std('posts', function ($t) {
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('excerpt', 300)->nullable();
            $t->text('body');
            $t->string('image')->nullable();
            $t->boolean('published')->default(false);
        });
        $this->std('experiences', function ($t) {
            $t->string('period');
            $t->string('title');
            $t->string('org')->nullable();
            $t->text('areas')->nullable();
        });
        $this->std('technologies', function ($t) {
            $t->string('name');
            $t->string('category');
            $t->string('description')->nullable();
            $t->string('level')->nullable();
            $t->boolean('visible')->default(true);
        });
        $this->std('skills', function ($t) {
            $t->string('title');
            $t->text('items')->nullable();
        });
        $this->std('services', function ($t) {
            $t->string('title');
            $t->text('description')->nullable();
        });
        $this->std('educations', function ($t) {
            $t->string('title');
            $t->string('institution')->nullable();
            $t->string('period')->nullable();
            $t->text('details')->nullable();
        });
        $this->std('certifications', function ($t) {
            $t->string('title');
            $t->string('issuer')->nullable();
            $t->string('year')->nullable();
            $t->string('url')->nullable();
        });
        $this->std('achievements', function ($t) {
            $t->string('title');
            $t->string('year')->nullable();
            $t->text('details')->nullable();
        });
        $this->std('workflows', function ($t) {
            $t->string('title');
            $t->text('description')->nullable();
        });
        $this->std('stats', function ($t) {
            $t->string('value');
            $t->string('label');
        });
        $this->std('reasons', function ($t) {
            $t->string('title');
        });
    }
    public function down(): void
    {
        foreach (['reasons', 'stats', 'workflows', 'achievements', 'certifications', 'educations', 'services', 'skills', 'technologies', 'experiences', 'posts', 'projects', 'messages', 'settings'] as $t) Schema::dropIfExists($t);
    }
};
