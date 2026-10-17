<?php
// Defines every editable section in the admin panel: [type, validation rules, options].
// Types: input, textarea, rich (full editor), richs (short editor), select, check, number, file, service, category, password
$cat = ['Web Design', 'Web Development', 'Laravel', 'Business', 'E-commerce', 'Blog/News', 'Digital'];
$tc = ['Frontend', 'Backend', 'Database', 'Tools', 'Web & Hosting', 'Digital', 'Graphics'];
$lv = ['', 'Familiar', 'Working Knowledge', 'Practical Experience', 'Experienced'];
$sort = ['number', 'nullable|integer'];
$seo = ['seo_title' => ['input', 'nullable|max:120'], 'seo_description' => ['textarea', 'nullable|max:170']];
return [
    'projects' => [
        'label' => 'Projects',
        'title' => 'title',
        'sub' => 'category',
        'f' => [
            'title' => ['input', 'required|max:150'],
            'category' => ['select', 'required', $cat],
            'description' => ['richs', 'nullable'],
            'tech' => ['input', 'nullable|max:255'],
            'features' => ['textarea', 'nullable'],
            'client' => ['input', 'nullable|max:150'],
            'year' => ['input', 'nullable|max:10'],
            'my_role' => ['input', 'nullable|max:150'],
            'challenge' => ['richs', 'nullable'],
            'solution' => ['rich', 'nullable'],
            'result' => ['richs', 'nullable'],
            'details' => ['rich', 'nullable'],
            'demo' => ['input', 'nullable|url'],
            'code' => ['input', 'nullable|url'],
            'image' => ['file', 'nullable|image|max:2048'],
            'sort' => $sort
        ] + $seo
    ],

    'posts' => [
        'label' => 'Blog posts',
        'title' => 'title',
        'sub' => 'slug',
        'f' => [
            'title' => ['input', 'required|max:200'],
            'slug' => ['input', 'nullable|alpha_dash|max:160'],
            'excerpt' => ['richs', 'nullable|max:1500'],
            'body' => ['rich', 'required'],
            'image' => ['file', 'nullable|image|max:2048'],
            'category' => ['category', 'nullable|max:160'],
            'published' => ['check', 'nullable'],
            'has_affiliate' => ['check', 'nullable'],
            'comments_open' => ['check', 'nullable']
        ] + $seo
    ],

    'experiences' => [
        'label' => 'Experience',
        'title' => 'title',
        'sub' => 'period',
        'f' => [
            'period' => ['input', 'required|max:60'],
            'title' => ['input', 'required|max:150'],
            'org' => ['input', 'nullable|max:200'],
            'areas' => ['textarea', 'nullable'],
            'sort' => $sort
        ]
    ],

    'technologies' => [
        'label' => 'Technology stack',
        'title' => 'name',
        'sub' => 'category',
        'f' => [
            'name' => ['input', 'required|max:80'],
            'category' => ['select', 'required', $tc],
            'description' => ['input', 'nullable|max:200'],
            'level' => ['select', 'nullable|max:40', $lv],
            'visible' => ['check', 'nullable'],
            'sort' => $sort
        ]
    ],

    'skills' => [
        'label' => 'Skills',
        'title' => 'title',
        'f' => [
            'title' => ['input', 'required|max:100'],
            'items' => ['textarea', 'nullable'],
            'sort' => $sort
        ]
    ],

    'services' => [
        'label' => 'Services',
        'title' => 'title',
        'f' => [
            'title' => ['input', 'required|max:100'],
            'slug' => ['input', 'nullable|alpha_dash|max:160'],
            'icon' => ['input', 'nullable|regex:/^bi-[a-z0-9-]+$/'],
            'description' => ['richs', 'nullable'],
            'details' => ['rich', 'nullable'],
            'sort' => $sort
        ] + $seo
    ],

    'educations' => [
        'label' => 'Education',
        'title' => 'title',
        'sub' => 'institution',
        'f' => [
            'title' => ['input', 'required|max:150'],
            'institution' => ['input', 'nullable|max:200'],
            'period' => ['input', 'nullable|max:60'],
            'details' => ['richs', 'nullable'],
            'sort' => $sort
        ]
    ],

    'certifications' => [
        'label' => 'Certifications',
        'title' => 'title',
        'sub' => 'issuer',
        'f' => [
            'title' => ['input', 'required|max:150'],
            'issuer' => ['input', 'nullable|max:150'],
            'year' => ['input', 'nullable|max:10'],
            'url' => ['input', 'nullable|url'],
            'sort' => $sort
        ]
    ],

    'achievements' => [
        'label' => 'Achievements',
        'title' => 'title',
        'sub' => 'year',
        'f' => [
            'title' => ['input', 'required|max:150'],
            'year' => ['input', 'nullable|max:10'],
            'details' => ['richs', 'nullable'],
            'sort' => $sort
        ]
    ],

    'workflows' => [
        'label' => 'Workflow steps',
        'title' => 'title',
        'f' => [
            'title' => ['input', 'required|max:80'],
            'description' => ['richs', 'nullable'],
            'sort' => $sort
        ]
    ],

    'stats' => [
        'label' => 'Statistics',
        'title' => 'value',
        'sub' => 'label',
        'f' => [
            'value' => ['input', 'required|max:30'],
            'label' => ['input', 'required|max:100'],
            'sort' => $sort
        ]
    ],

    'reasons' => [
        'label' => 'Why work with me',
        'title' => 'title',
        'f' => [
            'title' => ['input', 'required|max:150'],
            'sort' => $sort
        ]
    ],

    'categories' => [
        'label' => 'Blog categories',
        'title' => 'title',
        'sub' => 'slug',
        'f' => [
            'title' => ['input', 'required|max:100'],
            'slug' => ['input', 'nullable|alpha_dash|max:160'],
            'sort' => $sort
        ]
    ],

    'faqs' => [
        'label' => 'FAQs',
        'title' => 'question',
        'sub' => 'service',
        'f' => [
            'question' => ['input', 'required|max:200'],
            'answer' => ['richs', 'required|max:6000'],
            'service' => ['service', 'nullable|max:160'],
            'sort' => $sort
        ]
    ],

    'testimonials' => [
        'label' => 'Testimonials',
        'title' => 'name',
        'sub' => 'role',
        'f' => [
            'name' => ['input', 'required|max:100'],
            'role' => ['input', 'nullable|max:150'],
            'quote' => ['textarea', 'required|max:1000'],
            'photo' => ['file', 'nullable|image|max:4096'],
            'url' => ['input', 'nullable|url'],
            'permission' => ['check', 'accepted'],
            'visible' => ['check', 'nullable'],
            'sort' => $sort
        ]
    ],
];
