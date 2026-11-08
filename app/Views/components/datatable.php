<?php
/**
 * DataTable Component
 * 
 * @param string $id           شناسه جدول
 * @param array  $columns      ستون‌ها [['title'=>'عنوان','data'=>'field','width'=>'100px','sortable'=>true]]
 * @param array  $rows         داده‌ها
 * @param array  $options      تنظیمات اضافی
 * @param bool   $responsive   ریسپانسیو باشد
 * @param bool   $searchable   قابلیت جستجو
 * @param bool   $pagination   صفحه‌بندی
 * @param string $emptyMessage پیام خالی
 */
$id = $id ?? 'dataTable';
$columns = $columns ?? [];
$rows = $rows ?? [];
$options = $options ?? [];
$responsive = $responsive ?? true;
$searchable = $searchable ?? true;
$pagination = $pagination ?? true;
$emptyMessage = $emptyMessage ?? 'داده‌ای یافت نشد';
$showFooter = $showFooter ?? false;
$footerData = $footerData ?? [];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><?= $options['title'] ?? 'لیست داده‌ها' ?></h6>
        <div>
            <?php if ($options['showExport'] ?? false): ?>
            <div class="btn-group btn-group-sm me-1">
                <button class="btn btn-outline-success" onclick="exportTable('<?= $id ?>','excel')" title="Excel"><i class="fas fa-file-excel"></i></button>
                <button class="btn btn-outline-danger" onclick="exportTable('<?= $id ?>','pdf')" title="PDF"><i class="fas fa-file-pdf"></i></button>
                <button class="btn btn-outline-primary" onclick="window.print()" title="چاپ"><i class="fas fa-print"></i></button>
            </div>
            <?php endif; ?>
            <?php if ($options['showCount'] ?? true): ?>
            <span class="badge bg-primary rounded-pill"><?= count($rows) ?> رکورد</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="<?= $id ?>" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <?php if ($options['showRowNumber'] ?? true): ?>
                        <th width="5%">#</th>
                        <?php endif; ?>
                        <?php foreach ($columns as $col): ?>
                        <th <?= !empty($col['width']) ? "width=\"{$col['width']}\"" : '' ?> 
                            class="<?= ($col['sortable'] ?? true) ? 'sortable' : '' ?>">
                            <?= htmlspecialchars($col['title']) ?>
                        </th>
                        <?php endforeach; ?>
                        <?php if (!empty($options['actions'])): ?>
                        <th width="<?= $options['actionsWidth'] ?? '10%' ?>">عملیات</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $i => $row): ?>
                        <tr class="<?= $row['_rowClass'] ?? '' ?>" data-id="<?= $row['id'] ?? '' ?>">
                            <?php if ($options['showRowNumber'] ?? true): ?>
                            <td><?= $i + 1 ?></td>
                            <?php endif; ?>
                            <?php foreach ($columns as $col): 
                                $field = $col['data'] ?? '';
                                $value = $row[$field] ?? '';
                                $type = $col['type'] ?? 'text';
                            ?>
                            <td class="<?= $col['class'] ?? '' ?>">
                                <?php
                                switch ($type) {
                                    case 'badge':
                                        $badgeColor = $col['colors'][$value] ?? 'secondary';
                                        echo "<span class=\"badge bg-{$badgeColor}\">" . htmlspecialchars($col['labels'][$value] ?? $value) . "</span>";
                                        break;
                                    case 'image':
                                        $src = $value ?: ($col['default'] ?? 'assets/images/default-avatar.png');
                                        echo "<img src=\"{$src}\" class=\"rounded-circle\" width=\"{$col['imgWidth'] ?? 38}\" height=\"{$col['imgHeight'] ?? 38}\">";
                                        break;
                                    case 'code':
                                        echo "<code>" . htmlspecialchars($value) . "</code>";
                                        break;
                                    case 'date':
                                        echo $value ? date('Y/m/d', strtotime($value)) : '-';
                                        break;
                                    case 'datetime':
                                        echo $value ? date('Y/m/d H:i', strtotime($value)) : '-';
                                        break;
                                    case 'number':
                                        echo number_format($value);
                                        break;
                                    case 'currency':
                                        echo number_format($value) . ' تومان';
                                        break;
                                    case 'progress':
                                        $pct = min(100, max(0, (int)$value));
                                        $pctColor = $col['colorCallback'] ? $col['colorCallback']($pct) : ($pct > 80 ? 'danger' : ($pct > 50 ? 'warning' : 'success'));
                                        echo "<div class=\"d-flex align-items-center\"><span class=\"me-2 small\">{$pct}%</span><div class=\"progress flex-grow-1\" style=\"height:6px\"><div class=\"progress-bar bg-{$pctColor}\" style=\"width:{$pct}%\"></div></div></div>";
                                        break;
                                    case 'html':
                                        echo $col['render'] ? $col['render']($row) : $value;
                                        break;
                                    default:
                                        echo htmlspecialchars($value);
                                }
                                ?>
                            </td>
                            <?php endforeach; ?>
                            <?php if (!empty($options['actions'])): ?>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php foreach ($options['actions'] as $action): 
                                        $url = $action['url']($row) ?? '#';
                                        $icon = $action['icon'] ?? 'fa-eye';
                                        $color = $action['color'] ?? 'info';
                                        $title = $action['title'] ?? '';
                                        $onclick = $action['onclick'] ?? null;
                                        $showCondition = $action['showCondition'] ?? true;
                                        if (!$showCondition || (is_callable($showCondition) && !$showCondition($row))) continue;
                                    ?>
                                    <?php if ($onclick): ?>
                                    <button class="btn btn-<?= $color ?>" title="<?= $title ?>" onclick="<?= $onclick ?>('<?= $row['id'] ?? '' ?>')"><i class="fas <?= $icon ?>"></i></button>
                                    <?php else: ?>
                                    <a href="<?= $url ?>" class="btn btn-<?= $color ?>" title="<?= $title ?>"><i class="fas <?= $icon ?>"></i></a>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= count($columns) + ($options['showRowNumber'] ?? true ? 1 : 0) + (!empty($options['actions']) ? 1 : 0) ?>" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-2 d-block"></i><?= $emptyMessage ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if ($showFooter && !empty($footerData)): ?>
                <tfoot class="table-light fw-bold"><tr><?php foreach ($footerData as $fd): ?><td><?= htmlspecialchars($fd) ?></td><?php endforeach; ?></tr></tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php if ($searchable || $pagination): ?>
<script>
$(document).ready(function(){ $('#<?=$id?>').DataTable({responsive:<?=$responsive?'true':'false'?>,paging:<?=$pagination?'true':'false'?>,searching:<?=$searchable?'true':'false'?>,info:false,language:{url:'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json'},dom:'<?= $searchable ? "f" : "" ?><?= $pagination ? "p" : "" ?>',pageLength:<?= $options['pageLength'] ?? 25 ?>}); });
function exportTable(id,format){const table=$('#'+id).DataTable();if(format==='excel'){const wb=XLSX.utils.table_to_book(document.getElementById(id));XLSX.writeFile(wb,'export_'+new Date().toISOString().slice(0,10)+'.xlsx')}}
</script>
<?php endif; ?>