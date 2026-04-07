const input = document.getElementById("search");
const panel = document.getElementById("results");
const countEl = document.getElementById("count");
const clearBtn = document.getElementById("clearBtn");

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

if (input) {
    input.addEventListener("input", () => {
        clearTimeout(debounce);
        const q = input.value.trim();

        clearBtn.style.display = q ? "flex" : "none";

        if (!q) {
            panel.style.display = "none";
            panel.innerHTML = "";
            countEl.textContent = "";
            return;
        }

        debounce = setTimeout(() => runSearch(q), 300);
    });
}

async function runSearch(q) {
    try {
        const url = `/crochet/search/search_items.php?ajax=1&type=${encodeURIComponent(SEARCH_TYPE)}&q=${encodeURIComponent(q)}`;
        const res = await fetch(url);

        const text = await res.text();

        let items;
        try {
            items = JSON.parse(text);
        } catch (e) {
            console.error("Invalid JSON response:", text);
            panel.style.display = "none";
            panel.innerHTML = "";
            countEl.textContent = "Search backend error";
            return;
        }

        if (!Array.isArray(items) || items.length === 0) {
            panel.style.display = "none";
            panel.innerHTML = "";
            countEl.textContent = "No results found";
            return;
        }

        panel.innerHTML = items.map((it) => `
            <div class="item" data-id="${it.id}">
                <strong>${highlight(it.title || "", q)}</strong>
                <small>${highlight(it.description || "", q)}</small>
                ${it.category ? `<span class="item-category">${it.category}</span>` : ""}
            </div>
        `).join("");

        panel.style.display = "block";
        countEl.textContent = `${items.length} result(s) found`;
    } catch (error) {
        console.error("Fetch error:", error);
        panel.style.display = "none";
        panel.innerHTML = "";
        countEl.textContent = "Search failed";
    }
}

if (panel) {
    panel.addEventListener("mousedown", (e) => {
        const box = e.target.closest(".item");
        if (!box) return;

        const id = box.dataset.id;
        window.location.href = `${DETAIL_PAGE}?id=${id}`;
    });
}

if (clearBtn) {
    clearBtn.addEventListener("click", () => {
        input.value = "";
        clearBtn.style.display = "none";
        panel.style.display = "none";
        panel.innerHTML = "";
        countEl.textContent = "";
        input.focus();
    });
}