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
    var calm = matchMedia("(prefers-reduced-motion:reduce)").matches;
    var fine = matchMedia("(hover:hover) and (pointer:fine)").matches;

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
                    var hide = f !== "All" && i.dataset.cat !== f;
                    i.classList.toggle("d-none", hide);
                    if (!hide && !calm) {
                        i.classList.remove("pop");
                        void i.offsetWidth;
                        i.classList.add("pop");
                    }
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

    // scroll: progress bar, navbar (shadow, hide on scroll down), back to top, hero parallax
    var pg = $("prog"),
        nv = document.querySelector(".navbar"),
        tp = $("top"),
        tick = false,
        lastY = scrollY;
    var b1 = document.querySelector(".hero .b1"),
        b2 = document.querySelector(".hero .b2");
    function onScroll() {
        tick = false;
        var y = scrollY,
            h = root.scrollHeight - innerHeight;
        if (pg) pg.style.width = (h > 0 ? (y / h) * 100 : 0) + "%";
        if (nv) {
            nv.classList.toggle("sc", y > 20);
            var menu = $("nav"),
                open = menu && menu.classList.contains("show");
            if (open || y < 160) nv.classList.remove("nav-hide");
            else if (Math.abs(y - lastY) > 6) {
                nv.classList.toggle("nav-hide", y > lastY && y > 240);
                lastY = y;
            }
        }
        if (tp) tp.classList.toggle("d-none", y < 600);
        if (fine && !calm && b1 && y < 1200) {
            b1.style.transform =
                "translate3d(0," + (y * 0.09).toFixed(1) + "px,0)";
            if (b2)
                b2.style.transform =
                    "translate3d(0," + (-y * 0.06).toFixed(1) + "px,0)";
        }
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
    if (rot && !calm) {
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

    // testimonial slider: swipe, arrows, dots, keyboard, autoplay that pauses when you touch it
    $$("[data-slider]").forEach(function (sl) {
        var tr = sl.querySelector(".sl-track"),
            items = $$(".sl-item", tr),
            dots = sl.querySelector(".sl-dots"),
            wrap = sl.parentNode;
        var prev = wrap.querySelector("[data-sl-prev]"),
            next = wrap.querySelector("[data-sl-next]"),
            nav = wrap.querySelector(".sl-nav");
        var idx = 0,
            timer = null,
            hold = false,
            visible = false,
            sTick = false,
            built = false;
        function step() {
            return items.length > 1
                ? Math.max(1, items[1].offsetLeft - items[0].offsetLeft)
                : 1;
        }
        function perView() {
            return Math.max(1, Math.round(tr.clientWidth / step()));
        }
        function pages() {
            return Math.max(1, items.length - perView() + 1);
        }
        function mark() {
            idx = Math.min(
                pages() - 1,
                Math.max(0, Math.round(tr.scrollLeft / step())),
            );
            $$(".sl-dot", dots).forEach(function (d, i) {
                d.classList.toggle("on", i === idx);
            });
        }
        function go(i) {
            var n = pages();
            idx = (i + n) % n;
            tr.scrollTo({
                left: idx * step(),
                behavior: calm ? "auto" : "smooth",
            });
        }
        function build() {
            if (!tr.clientWidth) return;
            built = true;
            var n = pages();
            dots.innerHTML = "";
            if (nav) nav.style.visibility = n > 1 ? "" : "hidden";
            if (n < 2) return;
            for (var i = 0; i < n; i++) {
                var d = document.createElement("button");
                d.type = "button";
                d.className = "sl-dot";
                d.setAttribute("aria-label", "Go to review " + (i + 1));
                (function (k) {
                    d.onclick = function () {
                        go(k);
                    };
                })(i);
                dots.appendChild(d);
            }
            mark();
        }
        function play() {
            if (timer) clearInterval(timer);
            timer = null;
            if (calm || pages() < 2) return;
            timer = setInterval(function () {
                if (!hold && visible) go(idx + 1);
            }, 6500);
        }
        sl.addEventListener("pointerenter", function () {
            hold = true;
        });
        sl.addEventListener("pointerleave", function () {
            hold = false;
        });
        sl.addEventListener("focusin", function () {
            hold = true;
        });
        sl.addEventListener("focusout", function () {
            hold = false;
        });
        sl.addEventListener(
            "touchstart",
            function () {
                hold = true;
            },
            { passive: true },
        );
        sl.addEventListener(
            "touchend",
            function () {
                setTimeout(function () {
                    hold = false;
                }, 4000);
            },
            { passive: true },
        );
        tr.addEventListener(
            "scroll",
            function () {
                if (!sTick) {
                    sTick = true;
                    requestAnimationFrame(function () {
                        sTick = false;
                        mark();
                    });
                }
            },
            { passive: true },
        );
        tr.addEventListener("keydown", function (e) {
            if (e.key === "ArrowRight") {
                go(idx + 1);
                e.preventDefault();
            }
            if (e.key === "ArrowLeft") {
                go(idx - 1);
                e.preventDefault();
            }
        });
        if (prev)
            prev.onclick = function () {
                go(idx - 1);
            };
        if (next)
            next.onclick = function () {
                go(idx + 1);
            };
        var rT;
        addEventListener("resize", function () {
            clearTimeout(rT);
            rT = setTimeout(function () {
                build();
                play();
            }, 200);
        });
        if ("IntersectionObserver" in window) {
            new IntersectionObserver(
                function (e) {
                    visible = e[0].isIntersecting;
                    if (visible && !built) {
                        build();
                        play();
                    }
                },
                { threshold: 0.3 },
            ).observe(sl);
        } else {
            visible = true;
        }
        build();
        play();
    });

    // card tilt and light that follows the mouse (only on devices with a mouse)
    if (fine && !calm) {
        $$(
            ".card.tech,.card.proj,.card.svc,.card.step,.tile,.sl-item,.frame",
        ).forEach(function (c) {
            var raf = 0;
            c.addEventListener("pointermove", function (e) {
                if (raf) return;
                raf = requestAnimationFrame(function () {
                    raf = 0;
                    var r = c.getBoundingClientRect(),
                        x = (e.clientX - r.left) / r.width,
                        y = (e.clientY - r.top) / r.height;
                    c.style.setProperty("--mx", (x * 100).toFixed(1) + "%");
                    c.style.setProperty("--my", (y * 100).toFixed(1) + "%");
                    c.style.setProperty(
                        "--rx",
                        ((0.5 - y) * 6).toFixed(2) + "deg",
                    );
                    c.style.setProperty(
                        "--ry",
                        ((x - 0.5) * 8).toFixed(2) + "deg",
                    );
                    c.classList.add("tilt");
                });
            });
            c.addEventListener("pointerleave", function () {
                c.classList.remove("tilt");
                c.style.removeProperty("--rx");
                c.style.removeProperty("--ry");
            });
        });
        // big buttons lean a little towards the mouse
        $$(".hero .btn-lg,.cta-box .btn-lg").forEach(function (b) {
            b.addEventListener("pointermove", function (e) {
                var r = b.getBoundingClientRect();
                b.style.transform =
                    "translate(" +
                    ((e.clientX - r.left - r.width / 2) * 0.12).toFixed(1) +
                    "px," +
                    ((e.clientY - r.top - r.height / 2) * 0.25).toFixed(1) +
                    "px)";
            });
            b.addEventListener("pointerleave", function () {
                b.style.transform = "";
            });
        });
    }

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

    // icon safety net: if a page uses an icon that is not in the small icon file, switch to the full icon font
    (function () {
        if (!root.classList.contains("ic-s") || !window.IC_FULL) return;
        addEventListener("load", function () {
            setTimeout(function () {
                var miss = $$("i.bi").some(function (i) {
                    return !getComputedStyle(i).getPropertyValue("--bi").trim();
                });
                if (!miss) return;
                root.classList.remove("ic-s");
                var l = document.createElement("link");
                l.rel = "stylesheet";
                l.href = window.IC_FULL;
                document.head.appendChild(l);
                if (window.console)
                    console.warn(
                        "A missing icon was found, so the full icon font is used. Run: php artisan assets:icons",
                    );
            }, 400);
        });
    })();
})();
