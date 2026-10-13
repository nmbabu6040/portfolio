document.querySelectorAll("[data-rich]").forEach(function (el) {
    var input = document.getElementById(el.dataset.rich);
    var q = new Quill(el, {
        theme: "snow",
        modules: {
            toolbar: {
                container: [
                    [{ header: [2, 3, false] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ list: "ordered" }, { list: "bullet" }],
                    ["blockquote", "code-block"],
                    ["link", "image"],
                    ["clean"],
                ],
                handlers: {
                    image: function () {
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
                                        document.querySelector(
                                            "meta[name=csrf]",
                                        ).content,
                                    Accept: "application/json",
                                },
                                body: fd,
                            });
                            if (!r.ok) {
                                alert("Image upload failed");
                                return;
                            }
                            var j = await r.json();
                            var i = (
                                q.getSelection(true) || { index: q.getLength() }
                            ).index;
                            q.insertEmbed(i, "image", j.url);
                            q.setSelection(i + 1);
                        };
                        f.click();
                    },
                },
            },
        },
    });
    if (input.value) q.clipboard.dangerouslyPasteHTML(input.value);
    el.closest("form").addEventListener("submit", function () {
        input.value =
            q.getText().trim() === "" && !q.root.querySelector("img")
                ? ""
                : q.root.innerHTML;
    });
});

// drag-and-drop reorder
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

// media library: copy image URL
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

// image picker: choose an image from the media library
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
