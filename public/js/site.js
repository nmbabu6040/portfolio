(function () {
    "use strict";
    var $$ = function (s, r) {
        return [].slice.call((r || document).querySelectorAll(s));
    };
    var $ = function (id) {
        return document.getElementById(id);
    };
    var token = (document.querySelector("meta[name=csrf-token]") || {}).content;
    var root = document.documentElement;

    // filters (technology and projects)
    $$("[data-filters]").forEach(function (g) {
        g.addEventListener("click", function (e) {
            var b = e.target.closest("[data-f]");
            if (!b) return;
            var f = b.dataset.f,
                t = document.querySelector(g.dataset.target);
            $$("[data-f]", g).forEach(function (x) {
                x.classList.toggle("on", x === b);
            });
            if (t)
                $$("[data-cat]", t).forEach(function (i) {
                    i.classList.toggle(
                        "d-none",
                        f !== "All" && i.dataset.cat !== f,
                    );
                });
        });
    });

    // scroll reveal and number counters
    function count(n) {
        var to = +n.dataset.to,
            sx = n.dataset.sx || "",
            t0 = performance.now();
        (function f(t) {
            var p = Math.min((t - t0) / 1300, 1);
            n.textContent = Math.round(to * p) + sx;
            if (p < 1) requestAnimationFrame(f);
        })(t0);
    }
    var rv = $$(".rv");
    if ("IntersectionObserver" in window) {
        var io = new IntersectionObserver(
            function (es) {
                es.forEach(function (e) {
                    if (!e.isIntersecting) return;
                    e.target.classList.add("in");
                    var n =
                        e.target.querySelector("[data-to]") ||
                        (e.target.dataset.to ? e.target : null);
                    if (n) count(n);
                    io.unobserve(e.target);
                });
            },
            { threshold: 0.15 },
        );
        rv.forEach(function (e) {
            io.observe(e);
        });
    } else
        rv.forEach(function (e) {
            e.classList.add("in");
        });

    // dark / light mode
    var th = $("theme");
    if (th)
        th.onclick = function () {
            var v = root.dataset.bsTheme === "dark" ? "light" : "dark";
            root.dataset.bsTheme = v;
            try {
                localStorage.setItem("th", v);
            } catch (e) {}
        };

    // project screenshot lightbox
    $$("[data-lb]").forEach(function (b) {
        b.addEventListener("click", function () {
            $("lbi").src = b.dataset.lb;
            bootstrap.Modal.getOrCreateInstance($("lb")).show();
        });
    });

    // scroll: progress bar, navbar shadow, back to top
    var pg = $("prog"),
        nv = document.querySelector(".navbar"),
        tp = $("top"),
        tick = false;
    function onScroll() {
        tick = false;
        var h = root.scrollHeight - innerHeight;
        if (pg) pg.style.width = (h > 0 ? (scrollY / h) * 100 : 0) + "%";
        if (nv) nv.classList.toggle("sc", scrollY > 20);
        if (tp) tp.classList.toggle("d-none", scrollY < 600);
    }
    addEventListener(
        "scroll",
        function () {
            if (!tick) {
                tick = true;
                requestAnimationFrame(onScroll);
            }
        },
        { passive: true },
    );
    onScroll();
    if (tp)
        tp.onclick = function () {
            scrollTo({ top: 0 });
        };

    // rotating words in the hero
    var rot = $("rot");
    if (rot && !matchMedia("(prefers-reduced-motion:reduce)").matches) {
        var w = rot.dataset.words
                .split("|")
                .map(function (s) {
                    return s.trim();
                })
                .filter(Boolean),
            wi = 0;
        if (w.length > 1)
            setInterval(function () {
                rot.style.opacity = 0;
                setTimeout(function () {
                    wi = (wi + 1) % w.length;
                    rot.textContent = w[wi];
                    rot.style.opacity = 1;
                }, 300);
            }, 2600);
    }

    // close the mobile menu after a click
    $$(".navbar-nav a").forEach(function (a) {
        a.addEventListener("click", function () {
            var c = $("nav");
            if (c && c.classList.contains("show"))
                bootstrap.Collapse.getOrCreateInstance(c).hide();
        });
    });

    // share: copy link and the phone share sheet
    $$("[data-copy]").forEach(function (b) {
        b.onclick = function () {
            var t = b.dataset.copy,
                l = b.querySelector("span"),
                o = l.textContent;
            var done = function () {
                l.textContent = "Copied!";
                setTimeout(function () {
                    l.textContent = o;
                }, 1800);
            };
            if (navigator.clipboard)
                navigator.clipboard.writeText(t).then(done);
            else {
                var x = document.createElement("textarea");
                x.value = t;
                document.body.appendChild(x);
                x.select();
                document.execCommand("copy");
                x.remove();
                done();
            }
        };
    });
    if (navigator.share)
        $$("[data-native]").forEach(function (b) {
            b.classList.remove("d-none");
            b.onclick = function () {
                navigator
                    .share({ title: b.dataset.title, url: b.dataset.url })
                    .catch(function () {});
            };
        });

    // cookie consent: Google Analytics loads only after "Accept"
    (function () {
        var id = window.GA_ID,
            box = $("cc");
        if (!id || !box) return;
        var v = null;
        try {
            v = localStorage.getItem("cc");
        } catch (e) {}
        function load() {
            if (window.gaLoaded) return;
            window.gaLoaded = 1;
            var s = document.createElement("script");
            s.async = 1;
            s.src = "https://www.googletagmanager.com/gtag/js?id=" + id;
            document.head.appendChild(s);
            window.dataLayer = window.dataLayer || [];
            window.gtag = function () {
                dataLayer.push(arguments);
            };
            gtag("js", new Date());
            gtag("config", id);
        }
        if (v === "yes") load();
        else if (v === null) box.classList.remove("d-none");
        $$("[data-cc]", box).forEach(function (b) {
            b.onclick = function () {
                try {
                    localStorage.setItem("cc", b.dataset.cc);
                } catch (e) {}
                box.classList.add("d-none");
                if (b.dataset.cc === "yes") load();
            };
        });
    })();

    // count clicks on important buttons (WhatsApp, phone, email, quote, book a call, CV)
    if (token && navigator.sendBeacon) {
        var kind = function (a) {
            if (a.closest(".share")) return null;
            if (a.dataset.cta) return a.dataset.cta;
            var h = a.getAttribute("href") || "";
            if (
                /^https?:\/\/(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)/i.test(
                    h,
                )
            )
                return "whatsapp";
            if (/^tel:/i.test(h)) return "phone";
            if (/^mailto:/i.test(h)) return "email";
            if (a.hasAttribute("download")) return "cv";
            try {
                var u = new URL(h, location.href);
                if (
                    u.origin === location.origin &&
                    /^\/quote\/?$/.test(u.pathname)
                )
                    return "quote";
            } catch (e) {}
            return null;
        };
        document.addEventListener(
            "click",
            function (e) {
                var a = e.target.closest && e.target.closest("a[href]");
                if (!a) return;
                var k = kind(a);
                if (!k) return;
                var fd = new FormData();
                fd.append("_token", token);
                fd.append("name", k);
                fd.append("path", location.pathname);
                navigator.sendBeacon("/cta", fd);
            },
            true,
        );
    }

    // CAPTCHA (Cloudflare Turnstile): added to the public forms, its script loads only when someone uses a form
    (function () {
        var key = window.TS_KEY;
        if (!key) return;
        var forms = $$("form[method=post]").filter(function (f) {
            return /\/(contact|quote|subscribe)$|\/comment$/.test(
                f.getAttribute("action") || "",
            );
        });
        if (!forms.length) return;
        var loaded = false;
        function load() {
            if (loaded) return;
            loaded = true;
            var s = document.createElement("script");
            s.src = "https://challenges.cloudflare.com/turnstile/v0/api.js";
            s.async = true;
            s.defer = true;
            document.head.appendChild(s);
        }
        forms.forEach(function (f) {
            var d = document.createElement("div");
            d.className = "cf-turnstile mt-3";
            d.setAttribute("data-sitekey", key);
            d.setAttribute("data-appearance", "interaction-only");
            f.appendChild(d);
            f.addEventListener("focusin", load, { once: true });
            f.addEventListener("pointerdown", load, { once: true });
        });
    })();

    // blog: like button, share counter, reply to a comment
    $$("[data-like]").forEach(function (b) {
        b.addEventListener("click", function () {
            fetch(b.dataset.like, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": b.dataset.token,
                    Accept: "application/json",
                },
            })
                .then(function (r) {
                    return r.json();
                })
                .then(function (j) {
                    b.classList.toggle("on", j.liked);
                    b.setAttribute("aria-pressed", j.liked);
                    b.querySelector(".n").textContent = j.count;
                    b.querySelector("i").className =
                        "bi " + (j.liked ? "bi-heart-fill" : "bi-heart");
                })
                .catch(function () {});
        });
    });
    var sbox = document.querySelector(".share[data-track]");
    if (sbox)
        sbox.addEventListener("click", function (e) {
            if (!e.target.closest(".share-b")) return;
            var fd = new FormData();
            fd.append("_token", sbox.dataset.token);
            if (navigator.sendBeacon)
                navigator.sendBeacon(sbox.dataset.track, fd);
            var n = document.querySelector("[data-shares]");
            if (n) n.textContent = (+n.textContent || 0) + 1;
        });
    var cf = $("cform");
    if (cf) {
        var pid = $("parent_id"),
            note = $("creply");
        $$("[data-reply]").forEach(function (b) {
            b.addEventListener("click", function () {
                pid.value = b.dataset.reply;
                note.querySelector("b").textContent = b.dataset.name;
                note.classList.remove("d-none");
                cf.scrollIntoView({ behavior: "smooth", block: "center" });
                cf.querySelector("[name=body]").focus({ preventScroll: true });
            });
        });
        var cc = document.querySelector("[data-cancel-reply]");
        if (cc)
            cc.addEventListener("click", function () {
                pid.value = "";
                note.classList.add("d-none");
            });
    }

    // project gallery viewer (images and videos)
    (function () {
        var dataEl = $("gal-data"),
            modalEl = $("gal");
        if (!dataEl || !modalEl) return;
        var items = JSON.parse(dataEl.textContent),
            stage = $("gal-stage"),
            cap = $("gal-cap"),
            cur = 0,
            modal;
        if (items.length < 2)
            $$(".gal-nav", modalEl).forEach(function (n) {
                n.style.display = "none";
            });
        function show(i) {
            cur = (i + items.length) % items.length;
            var it = items[cur];
            stage.innerHTML = "";
            if (it.t === "i") {
                var im = document.createElement("img");
                im.src = it.src;
                im.alt = it.cap || "";
                stage.appendChild(im);
            } else {
                var f = document.createElement("iframe");
                f.src = it.src;
                f.title = "Project video";
                f.allow =
                    "accelerometer; encrypted-media; picture-in-picture; fullscreen";
                f.allowFullscreen = true;
                f.referrerPolicy = "strict-origin-when-cross-origin";
                stage.appendChild(f);
            }
            cap.textContent = it.cap || "";
        }
        $$("[data-gal]").forEach(function (b) {
            b.addEventListener("click", function () {
                modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                show(+b.dataset.gal);
                modal.show();
            });
        });
        modalEl.querySelector(".gal-prev").onclick = function () {
            show(cur - 1);
        };
        modalEl.querySelector(".gal-next").onclick = function () {
            show(cur + 1);
        };
        modalEl.addEventListener("hidden.bs.modal", function () {
            stage.innerHTML = "";
        });
        document.addEventListener("keydown", function (e) {
            if (!modalEl.classList.contains("show")) return;
            if (e.key === "ArrowLeft") show(cur - 1);
            if (e.key === "ArrowRight") show(cur + 1);
        });
    })();
})();
