<div class="search-container">
    <div class="search-box">
        <span class="icon-left">🔍</span>
        <input id="search" type="text" placeholder="<?php echo htmlspecialchars($searchPlaceholder ?? 'Search...'); ?>">
        <button type="button" class="icon-clear" id="clearBtn">✕</button>
    </div>

    <div id="results" class="panel" style="display:none;"></div>
    <p id="count" class="count"></p>
</div>

<script>
    const SEARCH_TYPE = "<?php echo htmlspecialchars($searchType ?? ''); ?>";
    const DETAIL_PAGE = "<?php echo htmlspecialchars($detailPage ?? ''); ?>";
</script>
<script src="/crochet/search/item_search.js"></script>