<?php
// 1. Determine the search type based on the filename of the page that includes this file
$currentFile = basename($_SERVER['PHP_SELF']);

// Define defaults (Changed to 'all' so Home Page works)
$searchType = "all"; 
$detailPage = "/crochet/home.php"; 
$placeholder = "Search all products...";

// 2. Logic to detect current section
if (strpos($currentFile, 'bouquet') !== false) {
    $searchType = "bouquet";
    $detailPage = "/crochet/bouquet/bouquet.php";
    $placeholder = "Search bouquets...";
} elseif (strpos($currentFile, 'decor') !== false) {
    $searchType = "decor";
    $detailPage = "/crochet/decor/decor.php";
    $placeholder = "Search decor...";
} elseif (strpos($currentFile, 'fashion') !== false) {
    $searchType = "fashion";
    $detailPage = "/crochet/fashion/fashion.php";
    $placeholder = "Search fashion...";
}
?>

<script>
    const SEARCH_TYPE = "<?php echo htmlspecialchars($searchType); ?>";
    const DETAIL_PAGE = "<?php echo htmlspecialchars($detailPage); ?>";
</script>
<script src="/crochet/search/item_search.js"></script>

<div class="search-container">
    <div class="search-box">
        <span class="icon-left">🔍</span>
        <input id="search" type="text" placeholder="<?php echo htmlspecialchars($placeholder); ?>">
        <button type="button" class="icon-clear" id="clearBtn" style="display:none;">✕</button>
    </div>

    <div id="results" class="panel" style="display:none;"></div>
    <p id="count" class="count"></p>
</div>