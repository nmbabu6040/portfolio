@php
    $help = [
        'resume_enabled' => 'On: /resume is public. Off: only logged-in admins can preview it',
        'resume_phone' => 'Show your phone number on the resume',
        'resume_location' => 'For example Dhaka, Bangladesh',
        'resume_summary' => 'A short professional summary. Leave empty to use your Intro text',
        'resume_projects' => 'How many recent projects to list, from 0 to 8. Default is 4',
        'turnstile_site' => 'From Cloudflare > Turnstile. Clear this box to turn CAPTCHA off',
        'turnstile_secret' => 'Stored encrypted. Both keys are needed. Leave empty to keep the saved one',
        'company_address' => 'Shown on quotations and invoices',
        'tax_id' => 'For example your BIN or tax number. Shown on the PDFs',
        'payment_details' => 'Bank or mobile payment details, shown on invoices',
        'default_terms' => 'Default terms for new quotations and invoices',
        'default_currency' => 'For example BDT or USD. Default is BDT',
        'default_tax' => 'Default tax percent. Use 0 if you do not charge tax',
        'booking_url' => 'Your Calendly, Cal.com or Google Calendar appointment link. Must start with https://',
        'booking_embed' => 'On: the calendar opens inside your site on the /book page. Off: it opens in a new tab',
        'client' => 'Optional. Only name a client if they agree',
        'my_role' => 'Your role in this project, for example Designer and developer',
        'challenge' => 'Case study: what problem did the client have?',
        'solution' => 'Case study: what did you do? Screenshots can go here',
        'result' => 'Case study: the real outcome. Do not invent numbers or results',
        'comments_open' => 'Visitors can comment on this post (comments need your approval first)',
        'tech' => 'Comma separated',
        'features' => 'One per line',
        'areas' => 'Comma separated',
        'items' => 'Comma separated',
        'sort' => 'Lower number appears first',
        'cv' => 'PDF or Word, max 5 MB',
        'photo' => 'Auto-converted to WebP',
        'slug' => 'Leave empty to generate from the title',
        'seo_title' => 'Ideal: 50 to 60 characters',
        'seo_description' => 'Plain text. Ideal: 120 to 160 characters',
        'visible' => 'Show on the website',
        'published' => 'Visible on the blog',
        'has_affiliate' => 'Shows an affiliate disclosure at the top of this post',
        'affiliate_domains' =>
            'One domain per line, e.g. amzn.to. Links to these domains get rel="sponsored nofollow" automatically',
        'affiliate_disclosure' => 'Leave empty to show the default text on /affiliate-disclosure',
        'ga_id' => 'Example: G-ABC123XYZ',
        'gsc_verify' => 'Only the content value of the Search Console meta tag',
        'mail_host' => 'Gmail: smtp.gmail.com (port 587, tls, use an App Password)',
        'mail_from' => 'Sender address (usually same as username)',
        'mail_password' => 'Stored encrypted. Leave blank to keep the current one.',
        'whatsapp' => 'With country code, e.g. +8801XXXXXXXXX',
        'service' => 'Pick a service to show it on that service page, or General for the Services page',
        'quote' => "The client's own words. Do not edit them to sound better.",
        'permission' =>
            'Required: tick only if this is a real client and you have their permission to publish this review',
    ];
@endphp
@foreach ($f as $n => $x)
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ ucwords(str_replace('_', ' ', $n)) }}</label>
        @if ($x[0] == 'textarea')
            <textarea name="{{ $n }}" rows="3" class="form-control">{{ old($n, data_get($v, $n, '')) }}</textarea>
        @elseif(in_array($x[0], ['rich', 'richs']))
            <input type="hidden" id="r_{{ $n }}" name="{{ $n }}"
                value="{{ old($n, data_get($v, $n, '')) }}">
            <div class="rich-box">
                <div data-rich="r_{{ $n }}" data-mode="{{ $x[0] == 'rich' ? 'full' : 'short' }}"
                    style="min-height:{{ $x[0] == 'rich' ? 280 : 110 }}px"></div>
            </div>
        @elseif($x[0] == 'password')
            <input type="password" name="{{ $n }}" class="form-control" autocomplete="new-password"
                placeholder="{{ data_get($v, $n) ? '•••••••• (saved)' : '' }}">
        @elseif($x[0] == 'check')
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                    name="{{ $n }}" value="1" @checked(old($n, data_get($v, $n, in_array($n, ['visible', 'comments_open']))))></div>
        @elseif($x[0] == 'select')
            <select name="{{ $n }}" class="form-select">
                @foreach ($x[2] as $c)
                    <option value="{{ $c }}" @selected(old($n, data_get($v, $n, '')) == $c)>{{ $c ?: '— none —' }}</option>
                @endforeach
            </select>
        @elseif($x[0] == 'service')
            <select name="{{ $n }}" class="form-select">
                <option value="">General (shown on the Services page)</option>
                @foreach (\App\Models\Entry::of('services')->newQuery()->orderBy('sort')->get(['title', 'slug']) as $sv)
                    <option value="{{ $sv->slug }}" @selected(old($n, data_get($v, $n, '')) == $sv->slug)>{{ $sv->title }}</option>
                @endforeach
            </select>
        @elseif($x[0] == 'category')
            <select name="{{ $n }}" class="form-select">
                <option value="">No category</option>
                @foreach (\App\Models\Entry::of('categories')->newQuery()->orderBy('sort')->get(['title', 'slug']) as $c)
                    <option value="{{ $c->slug }}" @selected(old($n, data_get($v, $n, '')) == $c->slug)>{{ $c->title }}</option>
                @endforeach
            </select>
        @elseif($x[0] == 'number')
            <input type="number" name="{{ $n }}" value="{{ old($n, data_get($v, $n, 0)) }}"
                class="form-control">
        @elseif($x[0] == 'file' && str_contains($x[1], 'image'))
            <div class="imgpick" data-pickfield>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                    <img class="pick-prev {{ data_get($v, $n) ? '' : 'd-none' }}"
                        src="{{ data_get($v, $n) ? asset(data_get($v, $n)) : '' }}" alt="">
                    <div class="small text-body-secondary pick-note">
                        {{ data_get($v, $n) ? 'Current: ' . data_get($v, $n) : 'No image yet' }}</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <input type="file" name="{{ $n }}" class="form-control" style="max-width:340px"
                        accept="image/*">
                    <button type="button" class="btn btn-outline-secondary" data-pick><i
                            class="bi bi-images me-1"></i>Choose from library</button>
                </div>
                <input type="hidden" name="{{ $n }}_pick" value="">
            </div>
        @elseif($x[0] == 'file')
            <input type="file" name="{{ $n }}" class="form-control">
            @if (data_get($v, $n))
                <small class="text-body-secondary">Current: {{ data_get($v, $n) }}</small>
            @endif
        @else
            <input name="{{ $n }}" value="{{ old($n, data_get($v, $n, '')) }}" class="form-control">
        @endif
        @if (!empty($help[$n]))
            <div class="form-text">{{ $help[$n] }}</div>
        @endif
        @error($n)
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
@endforeach
