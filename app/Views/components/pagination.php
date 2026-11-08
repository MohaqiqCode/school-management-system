<?php
/**
 * Pagination Component
 * 
 * Usage:
 *   echo pagination($currentPage, $totalPages, '/students', ['search'=>$search]);
 */
function pagination($currentPage, $totalPages, $url, $params = [], $options = []) {
    if ($totalPages <= 1) return '';
    $maxVisible = $options['maxVisible'] ?? 7;
    $currentPage = max(1, (int)$currentPage);
    $totalPages = max(1, (int)$totalPages);
    $queryString = !empty($params) ? '&' . http_build_query($params) : '';
    
    $pages = [];
    if ($totalPages <= $maxVisible) {
        for ($i = 1; $i <= $totalPages; $i++) $pages[] = $i;
    } else {
        $pages[] = 1;
        $start = max(2, $currentPage - 2);
        $end = min($totalPages - 1, $currentPage + 2);
        if ($start > 2) $pages[] = '...';
        for ($i = $start; $i <= $end; $i++) $pages[] = $i;
        if ($end < $totalPages - 1) $pages[] = '...';
        $pages[] = $totalPages;
    }
    
    ob_start();
?>
<nav aria-label="Page navigation"><ul class="pagination justify-content-center">
    <?php if ($currentPage > 1): ?><li class="page-item"><a class="page-link" href="<?=$url?>?page=<?=$currentPage-1?><?=$queryString?>"><i class="fas fa-chevron-right"></i></a></li><?php endif; ?>
    <?php foreach ($pages as $page): ?>
        <?php if ($page === '...'): ?><li class="page-item disabled"><span class="page-link">...</span></li>
        <?php elseif ($page == $currentPage): ?><li class="page-item active"><span class="page-link"><?=$page?></span></li>
        <?php else: ?><li class="page-item"><a class="page-link" href="<?=$url?>?page=<?=$page?><?=$queryString?>"><?=$page?></a></li><?php endif; ?>
    <?php endforeach; ?>
    <?php if ($currentPage < $totalPages): ?><li class="page-item"><a class="page-link" href="<?=$url?>?page=<?=$currentPage+1?><?=$queryString?>"><i class="fas fa-chevron-left"></i></a></li><?php endif; ?>
</ul>
<div class="text-center text-muted small mt-1">صفحه <?=$currentPage?> از <?=$totalPages?> | نمایش <?=$options['perPage']??25?> رکورد در هر صفحه</div></nav>
<?php return ob_get_clean(); }
?>