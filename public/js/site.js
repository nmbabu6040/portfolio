(function () {
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];
    // filters (stack + projects)
    $$("[data-filters]").forEach((g) =>
        g.addEventListener("click", (e) => {
            const b = e.target.closest("[data-f]");
            if (!b) return;
            const f = b.dataset.f;
            $$("[data-f]", g).forEach((x) => x.classList.toggle("on", x === b));
            $$("[data-cat]", document.querySelector(g.dataset.target)).forEach(
                (i) =>
                    i.classList.toggle(
                        "d-none",
                        f !== "All" && i.dataset.cat !== f,
                    ),
            );
        }),
    );
    // reveal + counters
    const io = new IntersectionObserver(
        (es) =>
            es.forEach((e) => {
                if (!e.isIntersecting) return;
                e.target.classList.add("in");
                const n =
                    e.target.querySelector("[data-to]") ||
                    (e.target.dataset.to ? e.target : null);
                if (n) {
                    const to = +n.dataset.to,
                        t0 = performance.now();
                    (function f(t) {
                        const p = Math.min((t - t0) / 1300, 1);
                        n.textContent =
                            Math.round(to * p) + (n.dataset.sx || "");
                        if (p < 1) requestAnimationFrame(f);
                    })(t0);
                }
                io.unobserve(e.target);
            }),
        { threshold: 0.15 },
    );
    $$(".rv").forEach((e) => io.observe(e));
    // theme
    const r = document.documentElement,
        th = document.getElementById("theme");
    if (th)
        th.onclick = () => {
            const v = r.dataset.bsTheme === "dark" ? "light" : "dark";
            r.dataset.bsTheme = v;
            try {
                localStorage.setItem("th", v);
            } catch (e) {}
        };
    // lightbox
    $$("[data-lb]").forEach((b) =>
        b.addEventListener("click", () => {
            document.getElementById("lbi").src = b.dataset.lb;
            new bootstrap.Modal("#lb").show();
        }),
    );
    // scroll: progress bar, navbar shadow, back-to-top
    const pg = document.getElementById("prog"),
        nv = document.querySelector(".navbar"),
        tp = document.getElementById("top");
    const onS = () => {
        const h = r.scrollHeight - innerHeight;
        pg.style.width = (h > 0 ? (scrollY / h) * 100 : 0) + "%";
        nv.classList.toggle("sc", scrollY > 20);
        tp.classList.toggle("d-none", scrollY < 600);
    };
    addEventListener("scroll", onS, { passive: true });
    onS();
    tp.onclick = () => scrollTo({ top: 0 });
    // rotating words in hero
    const rot = document.getElementById("rot");
    if (rot && !matchMedia("(prefers-reduced-motion:reduce)").matches) {
        const w = rot.dataset.words
            .split("|")
            .map((s) => s.trim())
            .filter(Boolean);
        let i = 0;
        setInterval(() => {
            rot.style.opacity = 0;
            setTimeout(() => {
                i = (i + 1) % w.length;
                rot.textContent = w[i];
                rot.style.opacity = 1;
            }, 300);
        }, 2600);
    }
    // close mobile menu after click
    $$(".navbar-nav a").forEach((a) =>
        a.addEventListener("click", () => {
            const c = document.getElementById("nav");
            if (c.classList.contains("show"))
                bootstrap.Collapse.getOrCreateInstance(c).hide();
        }),
    );
})();

// cookie consent: Google Analytics loads only after "Accept"
(function () {
    var id = window.GA_ID,
        box = document.getElementById("cc");
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
    box.querySelectorAll("[data-cc]").forEach(function (b) {
        b.onclick = function () {
            try {
                localStorage.setItem("cc", b.dataset.cc);
            } catch (e) {}
            box.classList.add("d-none");
            if (b.dataset.cc === "yes") load();
        };
    });
})();

// share: copy link + native share sheet
document.querySelectorAll("[data-copy]").forEach(function (b) {
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
        if (navigator.clipboard) navigator.clipboard.writeText(t).then(done);
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
    document.querySelectorAll("[data-native]").forEach(function (b) {
        b.classList.remove("d-none");
        b.onclick = function () {
            navigator
                .share({ title: b.dataset.title, url: b.dataset.url })
                .catch(function () {});
        };
    });

// blog: like button, share counter, reply to a comment
(function () {
    document.querySelectorAll("[data-like]").forEach(function (b) {
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
    var box = document.querySelector(".share[data-track]");
    if (box)
        box.addEventListener("click", function (e) {
            if (!e.target.closest(".share-b")) return;
            var fd = new FormData();
            fd.append("_token", box.dataset.token);
            if (navigator.sendBeacon)
                navigator.sendBeacon(box.dataset.track, fd);
            var n = document.querySelector("[data-shares]");
            if (n) n.textContent = (+n.textContent || 0) + 1;
        });
    var f = document.getElementById("cform");
    if (f) {
        var pid = document.getElementById("parent_id"),
            note = document.getElementById("creply");
        document.querySelectorAll("[data-reply]").forEach(function (b) {
            b.addEventListener("click", function () {
                pid.value = b.dataset.reply;
                note.querySelector("b").textContent = b.dataset.name;
                note.classList.remove("d-none");
                f.scrollIntoView({ behavior: "smooth", block: "center" });
                f.querySelector("[name=body]").focus({ preventScroll: true });
            });
        });
        var c = document.querySelector("[data-cancel-reply]");
        if (c)
            c.addEventListener("click", function () {
                pid.value = "";
                note.classList.add("d-none");
            });
    }
})();

// count clicks on important buttons (WhatsApp, phone, email, quote, book a call, CV)
(function () {
    var m = document.querySelector("meta[name=csrf-token]");
    if (!m || !navigator.sendBeacon) return;
    function kind(a) {
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
            if (u.origin === location.origin && /^\/quote\/?$/.test(u.pathname))
                return "quote";
        } catch (e) {}
        return null;
    }
    document.addEventListener(
        "click",
        function (e) {
            var a = e.target.closest && e.target.closest("a[href]");
            if (!a) return;
            var k = kind(a);
            if (!k) return;
            var fd = new FormData();
            fd.append("_token", m.content);
            fd.append("name", k);
            fd.append("path", location.pathname);
            navigator.sendBeacon("/cta", fd);
        },
        true,
    );
})();

// project gallery viewer (images and videos)
(function () {
    var dataEl = document.getElementById("gal-data"),
        modalEl = document.getElementById("gal");
    if (!dataEl || !modalEl) return;
    var items = JSON.parse(dataEl.textContent),
        stage = document.getElementById("gal-stage"),
        cap = document.getElementById("gal-cap"),
        cur = 0,
        modal;
    if (items.length < 2)
        modalEl.querySelectorAll(".gal-nav").forEach(function (n) {
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
    document.querySelectorAll("[data-gal]").forEach(function (b) {
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

// CAPTCHA (Cloudflare Turnstile): the widget is added to the public forms, and its script loads only when someone uses a form
(function () {
    var key = window.TS_KEY;
    if (!key) return;
    var forms = [].filter.call(
        document.querySelectorAll("form[method=post]"),
        function (f) {
            return /\/(contact|quote|subscribe)$|\/comment$/.test(
                f.getAttribute("action") || "",
            );
        },
    );
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
