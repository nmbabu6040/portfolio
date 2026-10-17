// ---------- rich text editors (Quill) ----------
(function () {
    function esc(s) {
        return s
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }
    // old plain-text values become paragraphs in the editor
    function toHtml(v) {
        if (!v) return "";
        if (/<\/?[a-z][\s\S]*>/i.test(v)) return v;
        return v
            .split(/\n{2,}/)
            .map(function (p) {
                return "<p>" + esc(p).replace(/\n/g, "<br>") + "</p>";
            })
            .join("");
    }
    var FULL = [
        [{ header: [2, 3, 4, false] }],
        ["bold", "italic", "underline", "strike"],
        [{ list: "ordered" }, { list: "bullet" }],
        ["blockquote", "code-block"],
        ["link", "image"],
        ["clean"],
    ];
    var SHORT = [
        ["bold", "italic", "underline"],
        [{ list: "ordered" }, { list: "bullet" }],
        ["link"],
        ["clean"],
    ];
    document.querySelectorAll("[data-rich]").forEach(function (el) {
        if (typeof Quill === "undefined") return;
        var input = document.getElementById(el.dataset.rich),
            full = el.dataset.mode !== "short",
            q;
        function pickImage() {
            var f = document.createElement("input");
            f.type = "file";
            f.accept = "image/*";
            f.onchange = async function () {
                var fd = new FormData();
                fd.append("image", f.files[0]);
                var r = await fetch("/admin/upload", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN":
                            document.querySelector("meta[name=csrf]").content,
                        Accept: "application/json",
                    },
                    body: fd,
                });
                if (!r.ok) {
                    alert("Image upload failed");
                    return;
                }
                var j = await r.json();
                var i = (q.getSelection(true) || { index: q.getLength() })
                    .index;
                q.insertEmbed(i, "image", j.url);
                q.setSelection(i + 1);
            };
            f.click();
        }
        q = new Quill(el, {
            theme: "snow",
            placeholder: full ? "Write here..." : "Write a short text...",
            modules: {
                toolbar: full
                    ? { container: FULL, handlers: { image: pickImage } }
                    : { container: SHORT },
            },
        });
        var html = toHtml(input.value);
        if (html) q.clipboard.dangerouslyPasteHTML(html);
        el.closest("form").addEventListener("submit", function () {
            input.value =
                q.getText().trim() === "" && !q.root.querySelector("img")
                    ? ""
                    : q.getSemanticHTML().replace(/&nbsp;/g, " ");
        });
    });
})();

// ---------- drag-and-drop reorder ----------
document.querySelectorAll("[data-sortable]").forEach(function (el) {
    var note = document.getElementById("sortnote");
    Sortable.create(el, {
        handle: ".drag",
        animation: 150,
        ghostClass: "drag-ghost",
        onEnd: function () {
            var ids = [].map.call(
                el.querySelectorAll("[data-id]"),
                function (r) {
                    return r.dataset.id;
                },
            );
            fetch(el.dataset.sortable, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN":
                        document.querySelector("meta[name=csrf]").content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ ids: ids }),
            })
                .then(function (r) {
                    if (!r.ok) throw 0;
                    if (note) {
                        note.textContent = "· Order saved";
                        note.className = "small text-success";
                    }
                })
                .catch(function () {
                    if (note) {
                        note.textContent =
                            "· Could not save the order, please reload and try again";
                        note.className = "small text-danger";
                    }
                });
        },
    });
});

// ---------- media library: copy image URL ----------
document.querySelectorAll("[data-copyurl]").forEach(function (b) {
    b.addEventListener("click", function () {
        var t = b.dataset.copyurl,
            old = b.innerHTML;
        var done = function () {
            b.textContent = "Copied!";
            setTimeout(function () {
                b.innerHTML = old;
            }, 1600);
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
    });
});

// ---------- image picker (media library popup) ----------
(function () {
    var modalEl = document.getElementById("picker");
    if (!modalEl) return;
    var grid = document.getElementById("picker-grid"),
        empty = document.getElementById("picker-empty"),
        modal,
        target,
        cache = null;
    function render(list) {
        grid.innerHTML = "";
        empty.classList.toggle("d-none", list.length > 0);
        list.forEach(function (it) {
            var b = document.createElement("button");
            b.type = "button";
            b.className = "pitem";
            b.title = it.path;
            var im = document.createElement("img");
            im.src = it.thumb;
            im.alt = "";
            im.loading = "lazy";
            b.appendChild(im);
            b.onclick = function () {
                target.querySelector("input[type=hidden]").value = it.path;
                var pv = target.querySelector(".pick-prev");
                pv.src = "/" + it.path;
                pv.classList.remove("d-none");
                target.querySelector(".pick-note").textContent =
                    "Selected from library. Click Save to apply.";
                var f = target.querySelector("input[type=file]");
                if (f) f.value = "";
                modal.hide();
            };
            grid.appendChild(b);
        });
    }
    document.querySelectorAll("[data-pick]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            target = btn.closest("[data-pickfield]");
            modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            if (cache) return render(cache);
            fetch("/admin/media/picker", {
                headers: { Accept: "application/json" },
            })
                .then(function (r) {
                    return r.json();
                })
                .then(function (l) {
                    cache = l;
                    render(l);
                });
        });
    });
})();

// leads pipeline: drag a card to another column to change its stage
(function () {
    var cols = document.querySelectorAll("[data-stage]");
    if (!cols.length || typeof Sortable === "undefined") return;
    function recount() {
        document.querySelectorAll(".bcol").forEach(function (c) {
            var n = c.querySelector("[data-count]");
            if (n) n.textContent = c.querySelectorAll(".lcard").length;
        });
    }
    cols.forEach(function (col) {
        Sortable.create(col, {
            group: "leads",
            animation: 150,
            ghostClass: "drag-ghost",
            delay: 150,
            delayOnTouchOnly: true,
            onAdd: function (e) {
                fetch("/admin/leads/" + e.item.dataset.id + "/stage", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN":
                            document.querySelector("meta[name=csrf]").content,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ stage: col.dataset.stage }),
                })
                    .then(function (r) {
                        if (!r.ok) throw 0;
                        recount();
                    })
                    .catch(function () {
                        alert("Could not move the lead. The page will reload.");
                        location.reload();
                    });
            },
        });
    });
})();
