@extends('layouts.app')
@php
    $name = $s['name'] ?? $p['name'];
    $em = $s['email'] ?? $p['email'];
@endphp
@section('title', 'Privacy Policy | ' . $name)
@section('desc', 'How ' . $name . ' collects, uses and protects information on this website.')
@section('content')
    @include('sections.pagehead', [
        'title' => 'Privacy Policy',
        'sub' => 'How information is collected and used on this website.',
    ])
    <section class="sec">
        <div class="container">
            <div class="post-body" style="max-width:820px">
                @if (!empty($s['privacy_policy']))
                    {!! \App\Support\Html::render($s['privacy_policy']) !!}
                @else
                    <h2>Who I am</h2>
                    <p>This website belongs to {{ $name }}. You can contact me at <a
                            href="mailto:{{ $em }}">{{ $em }}</a>.</p>
                    <h2>Information you send me</h2>
                    <p>If you use the contact form, I receive the details you enter: your name, email address, phone number
                        (optional), subject and message. I use them only to reply to you and to follow up on your enquiry.
                        Messages are stored in this website's database and may be forwarded to my email inbox. I do not sell
                        or share this information.</p>

                    <h2>How you found this website</h2>
                    <p>When you send a message, I also note which website or page you came from and the first page you
                        visited, so I know which channels bring visitors. I also count how many times buttons such as
                        WhatsApp, Book a call or Request a quote are clicked, together with the page name. No personal
                        details are stored for these counts.</p>

                    <h2>Cookies and local storage</h2>
                    <p>This site uses essential cookies for security and form protection. Your light or dark theme choice
                        and your cookie choice are stored in your browser's local storage.</p>
                    @if (!empty($s['ga_id']))
                        <p>If you accept, I use Google Analytics to understand how visitors use the site, for example which
                            pages are viewed. This data is collected in aggregate by Google. If you decline, Google
                            Analytics is not loaded.</p>
                    @endif

                    <h2>Comments and likes</h2>
                    <p>If you leave a comment on a blog post, I store the name, email address and message you enter,
                        together with the date. Your email address is never shown publicly, and comments appear after I
                        approve them. To limit spam and repeated likes, I store a short one-way code made from your
                        connection details instead of your IP address. You can ask me to delete your comment at any time.
                    </p>

                    <h2>Email updates</h2>
                    <p>If you subscribe to email updates, I store your email address and the date you subscribed. I use it
                        only to send you updates from this website, and every email includes a link to unsubscribe. You can
                        also ask me to delete your email address at any time.</p>

                    <h2>Visitor statistics</h2>
                    <p>This site counts page views in a simple way to understand which pages are visited. I store the page
                        address, the date, the device type (desktop, tablet or mobile), the referring website and a short
                        anonymous code that changes every day. I do not store IP addresses, and no cookies are used for this
                        counting.</p>

                    <h2>Third-party services</h2>
                    <p>This site loads fonts from Google Fonts and code, icons and logos from public content delivery
                        networks such as jsDelivr. These providers may receive your IP address and browser information when
                        your browser requests those files.</p>

                    <h2>How long I keep information</h2>
                    <p>I keep contact messages for as long as needed to respond and keep a record of the conversation, then
                        delete them.</p>

                    <h2>Your choices</h2>
                    <p>You can ask me to show or delete the information you sent by emailing <a
                            href="mailto:{{ $em }}">{{ $em }}</a>.</p>

                    <h2>Changes</h2>
                    <p>I may update this policy from time to time. The current version is always on this page.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
