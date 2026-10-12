(function () {
    var KEY = "dbglog",
        lines = [],
        head = "";
    var box = document.createElement("pre");
    box.style.cssText =
        "position:fixed;top:70px;right:8px;z-index:99999;width:380px;max-height:62vh;overflow:auto;margin:0;padding:8px;background:rgba(0,0,0,.88);color:#0f0;font:11px/1.4 monospace;white-space:pre-wrap;pointer-events:none";
    document.body.appendChild(box);
    try {
        var p = JSON.parse(sessionStorage.getItem(KEY) || "[]");
        if (p.length)
            head =
                "=== BEFORE THE PAGE RELOADED ===\n" +
                p.join("\n") +
                "\n=== NEW PAGE LOADED ===\n";
    } catch (x) {}
    function save() {
        try {
            sessionStorage.setItem(KEY, JSON.stringify(lines.slice(-12)));
        } catch (x) {}
    }
    function say(t) {
        lines.push(new Date().toLocaleTimeString() + " " + t);
        if (lines.length > 12) lines.shift();
        box.textContent = head + lines.join("\n");
        save();
    }
    function d(el) {
        if (!el || !el.tagName) return "null";
        return (
            el.tagName.toLowerCase() +
            (el.id ? "#" + el.id : "") +
            (typeof el.className === "string" && el.className.trim()
                ? "." + el.className.trim().split(/\s+/).slice(0, 2).join(".")
                : "")
        );
    }
    function who() {
        return (new Error().stack || "")
            .split("\n")
            .slice(2, 4)
            .join(" | ")
            .replace(/https?:\/\/[^/]+/g, "");
    }
    var lastY = scrollY;
    say("panel ready at " + location.pathname + " y=" + Math.round(scrollY));
    document.addEventListener(
        "pointerdown",
        function (e) {
            var t = e.target,
                top = document.elementFromPoint(e.clientX, e.clientY),
                a = t.closest && t.closest("a"),
                f = t.closest && t.closest("form");
            say(
                "CLICK " +
                    d(t) +
                    " top=" +
                    d(top) +
                    (a ? " INSIDE-LINK href=" + a.getAttribute("href") : "") +
                    (f
                        ? " INSIDE-FORM action=" + f.getAttribute("action")
                        : "") +
                    " y=" +
                    Math.round(scrollY),
            );
        },
        true,
    );
    document.addEventListener(
        "click",
        function (e) {
            var a = e.target.closest && e.target.closest("a");
            if (a)
                say(
                    "LINK " +
                        a.getAttribute("href") +
                        " prevented=" +
                        e.defaultPrevented,
                );
        },
        true,
    );
    document.addEventListener(
        "submit",
        function (e) {
            say(
                "FORM SUBMIT action=" +
                    e.target.getAttribute("action") +
                    " id=" +
                    (e.target.id || "-"),
            );
        },
        true,
    );
    document.addEventListener(
        "focusin",
        function (e) {
            say("FOCUS " + d(e.target));
        },
        true,
    );
    addEventListener(
        "scroll",
        function () {
            var y = scrollY;
            if (Math.abs(y - lastY) > 400)
                say("BIG SCROLL " + Math.round(lastY) + " -> " + Math.round(y));
            lastY = y;
        },
        { passive: true },
    );
    addEventListener("beforeunload", function () {
        say("PAGE UNLOADING at y=" + Math.round(scrollY));
    });
    addEventListener("hashchange", function () {
        say("hash=" + location.hash);
    });
    var oT = window.scrollTo;
    window.scrollTo = function () {
        say(
            "scrollTo " +
                JSON.stringify([].slice.call(arguments)) +
                " by " +
                who(),
        );
        return oT.apply(this, arguments);
    };
    var oI = Element.prototype.scrollIntoView;
    Element.prototype.scrollIntoView = function () {
        say("scrollIntoView " + d(this) + " by " + who());
        return oI.apply(this, arguments);
    };
})();
