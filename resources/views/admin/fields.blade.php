@php $help = ['tech' => 'Comma separated', 'features' => 'One per line', 'areas' => 'Comma separated', 'items' => 'Comma separated', 'sort' => 'Lower number appears first', 'about' => 'Separate paragraphs with a blank line', 'cv' => 'PDF or Word, max 5 MB', 'photo' => 'Auto-converted to WebP', 'slug' => 'Leave empty to generate from the title', 'seo_title' => 'Up to 70 characters', 'seo_description' => 'Up to 170 characters', 'visible' => 'Show on the website', 'published' => 'Visible on the blog', 'ga_id' => 'Example: G-ABC123XYZ', 'gsc_verify' => 'Only the content value of the Search Console meta tag', 'mail_host' => 'Gmail: smtp.gmail.com (port 587, tls, use an App Password)', 'mail_from' => 'Sender address (usually same as username)', 'mail_password' => 'Stored encrypted. Leave blank to keep the current one.']; @endphp
@foreach ($f as $n => $x)
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ ucwords(str_replace('_', ' ', $n)) }}</label>
        @if ($x[0] == 'textarea')
            <textarea name="{{ $n }}" rows="3" class="form-control">{{ old($n, data_get($v, $n, '')) }}</textarea>
        @elseif($x[0] == 'rich')
            <input type="hidden" id="r_{{ $n }}" name="{{ $n }}"
                value="{{ old($n, data_get($v, $n, '')) }}">
            <div data-rich="r_{{ $n }}" style="min-height:280px;background:#fff;color:#000"></div>
        @elseif($x[0] == 'password')
            <input type="password" name="{{ $n }}" class="form-control" autocomplete="new-password"
                placeholder="{{ data_get($v, $n) ? '•••••••• (saved)' : '' }}">
        @elseif($x[0] == 'check')
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                    name="{{ $n }}" value="1" @checked(old($n, data_get($v, $n, $n == 'visible')))></div>
        @elseif($x[0] == 'select')
            <select name="{{ $n }}" class="form-select">
                @foreach ($x[2] as $c)
                    <option value="{{ $c }}" @selected(old($n, data_get($v, $n, '')) == $c)>{{ $c ?: '— none —' }}</option>
                @endforeach
            </select>
        @elseif($x[0] == 'number')
            <input type="number" name="{{ $n }}" value="{{ old($n, data_get($v, $n, 0)) }}"
                class="form-control">
        @elseif($x[0] == 'file')
            <input type="file" name="{{ $n }}" class="form-control">
            @if (data_get($v, $n))
                <small class="text-body-secondary">Current: {{ data_get($v, $n) }}</small>
            @endif
        @elseif($x[0] == 'file')
            <input type="file" name="{{ $n }}" class="form-control">
            @if (data_get($v, $n))
                <small class="text-body-secondary">Current: {{ data_get($v, $n) }}</small>
            @endif
        @elseif($x[0] == 'file')
            a
            <input type="file" name="{{ $n }}" class="form-control">
            @if (data_get($v, $n))
                <small class="text-body-secondary">Current: {{ data_get($v, $n) }}</small>
            @endif
        @else<input name="{{ $n }}" value="{{ old($n, data_get($v, $n, '')) }}" class="form-control">
        @endif
        @if (!empty($help[$n]))
            <div class="form-text">{{ $help[$n] }}</div>
        @endif
        @error($n)
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
@endforeach
