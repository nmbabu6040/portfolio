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
