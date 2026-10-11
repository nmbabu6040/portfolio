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
