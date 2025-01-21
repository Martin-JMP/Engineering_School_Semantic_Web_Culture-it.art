document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("search-input");
    const searchResults = document.getElementById("search-results");

    searchInput.addEventListener("input", async () => {
        const query = searchInput.value.trim();
        if (query.length < 3) {
            searchResults.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`search_artworks_comments.php?query=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error("Error fetching search results");

            const results = await response.json();
            displaySearchResults(results);
        } catch (error) {
            console.error("Error:", error);
        }
    });

    function displaySearchResults(results) {
        searchResults.innerHTML = '';
        if (results.length === 0) {
            searchResults.innerHTML = '<p>No results found</p>';
            return;
        }

        const uniqueResults = [];
        const titles = new Set();

        results.forEach(result => {
            const title = result.title || result.comment;
            if (!titles.has(title)) {
                titles.add(title);
                uniqueResults.push(result);
            }
        });

        uniqueResults.slice(0, 5).forEach(result => {
            const resultItem = document.createElement("div");
            resultItem.className = "search-result-item";

            const title = document.createElement("div");
            title.className = "search-result-title";
            title.textContent = result.title || result.comment;

            const description = document.createElement("div");
            description.className = "search-result-description";
            description.textContent = result.description || result.comment;

            resultItem.appendChild(title);
            resultItem.appendChild(description);
            searchResults.appendChild(resultItem);
        });
    }
});
