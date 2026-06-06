document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("search");
    const panel = document.getElementById("results");
    const countEl = document.getElementById("count");
    const clearBtn = document.getElementById("clearBtn");

    // Exit immediately if search elements are missing from this page
    if (!input || !panel) return;

    let debounce = null;

    function highlight(text, q) {
        text = text || "";
        q = q.toLowerCase();
        const low = text.toLowerCase();
        const idx = low.indexOf(q);
        if (idx === -1) return text;
        return text.slice(0, idx) +
            `<span class="match">${text.slice(idx, idx + q.length)}</span>` +
            text.slice(idx + q.length);
    }

    input.addEventListener("input", () => {
        clearTimeout(debounce);
        const q = input.value.trim();

        if (clearBtn) clearBtn.style.display = q ? "flex" : "none";

        if (!q) {
            panel.style.display = "none";
            panel.innerHTML = "";
            if (countEl) countEl.textContent = "";
            return;
        }

        debounce = setTimeout(() => runSearch(q), 300);
    });

    async function runSearch(q) {
        // Use global variables defined in your HTML, or default to empty strings
        const type = typeof SEARCH_TYPE !== 'undefined' ? SEARCH_TYPE : "";
        
        try {
            const url = `/crochet/search/search_items.php?ajax=1&type=${encodeURIComponent(type)}&q=${encodeURIComponent(q)}`;
            const res = await fetch(url);
            
            if (!res.ok) throw new Error("Network response was not ok");
            
            const text = await res.text();

            let items;
            try {
                items = JSON.parse(text);
            } catch (e) {
                console.error("Invalid JSON response:", text);
                if (countEl) countEl.textContent = "Backend error";
                return;
            }

            if (!Array.isArray(items) || items.length === 0) {
                panel.style.display = "none";
                panel.innerHTML = "";
                if (countEl) countEl.textContent = "No results found";
                return;
            }

            // Render items
            panel.innerHTML = items.map((it) => `
                <div class="item" data-id="${it.id}">
                    <strong>${highlight(it.title || "", q)}</strong>
                    <small>${highlight(it.description || "", q)}</small>
                    ${it.category ? `<span class="item-category">${it.category}</span>` : ""}
                </div>
            `).join("");

            panel.style.display = "block";
            if (countEl) countEl.textContent = `${items.length} result(s) found`;
            
        } catch (error) {
            console.error("Search error:", error);
            if (countEl) countEl.textContent = "Search failed";
        }
    }

    // Handle clicks on items
    panel.addEventListener("mousedown", (e) => {
        const box = e.target.closest(".item");
        if (!box) return;

        const id = box.dataset.id;
        const page = typeof DETAIL_PAGE !== 'undefined' ? DETAIL_PAGE : "";
        if (page) {
            window.location.href = `${page}?id=${id}`;
        } else {
            console.error("DETAIL_PAGE is not defined");
        }
    });

    // Handle clear button
    if (clearBtn) {
        clearBtn.addEventListener("click", () => {
            input.value = "";
            clearBtn.style.display = "none";
            panel.style.display = "none";
            panel.innerHTML = "";
            if (countEl) countEl.textContent = "";
            input.focus();
        });
    }
});