<?php
$title = 'مدیریت کلاس‌ها';
$classes = $classes ?? [];

$content = function() use ($classes) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-chalkboard me-2"></i>مدیریت کلاس‌ها
    </h1>
    <?php if (hasPermission('create_classes')): ?>
    <a href="<?= url('/classes/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>ایجاد کلاس جدید
    </a>
    <?php endif; ?>
</div>

<!-- Classes Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>لیست کلاس‌ها</h6>
        <span class="badge bg-primary rounded-pill"><?= count($classes ?? []) ?> کلاس</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="classesTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="8%">پایه</th>
                        <th width="15%">کد کلاس</th>
                        <th width="20%">نام کلاس</th>
                        <th width="10%">شعبه</th>
                        <th width="8%">ظرفیت</th>
                        <th width="10%">بخش‌ها</th>
                        <th width="10%">دانش‌آموزان</th>
                        <th width="7%">وضعیت</th>
                        <th width="7%">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($classes)): ?>
                        <?php foreach ($classes as $i => $class): 
                            $occupancy = ($class['capacity'] ?? 1) > 0 ? round((($class['student_count'] ?? 0) / $class['capacity']) * 100) : 0;
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <span class="badge bg-secondary fs-6"><?= $class['class_level'] ?? '-' ?></span>
                            </td>
                            <td><code><?= htmlspecialchars($class['class_code'] ?? '') ?></code></td>
                            <td>
                                <a href="<?= url('/sections?class_id=' . $class['id']) ?>" class="text-decoration-none fw-bold">
                                    <?= htmlspecialchars($class['class_name'] ?? '') ?>
                                </a>
                                <?php if (!empty($class['description'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars(mb_substr($class['description'], 0, 50)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars($class['branch_name'] ?? '-') ?></small></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2 small"><?= $class['student_count'] ?? 0 ?>/<?= $class['capacity'] ?? 0 ?></span>
                                    <div class="progress flex-grow-1" style="height:5px;">
                                        <div class="progress-bar bg-<?= $occupancy > 80 ? 'danger' : ($occupancy > 50 ? 'warning' : 'success') ?>" 
                                             style="width:<?= $occupancy ?>%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-info"><?= $class['section_count'] ?? 0 ?> بخش</span></td>
                            <td><span class="badge bg-success"><?= $class['student_count'] ?? 0 ?> نفر</span></td>
                            <td>
                                <?php if ($class['is_active'] ?? false): ?>
                                <span class="badge bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge bg-danger">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if (hasPermission('edit_classes')): ?>
                                    <a href="<?= url('/classes/' . $class['id'] . '/edit') ?>" class="btn btn-warning" title="ویرایش" data-bs-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('/sections?class_id=' . $class['id']) ?>" class="btn btn-info" title="بخش‌ها" data-bs-toggle="tooltip">
                                        <i class="fas fa-layer-group"></i>
                                    </a>
                                    <?php if (hasPermission('delete_classes') && ($class['student_count'] ?? 0) == 0): ?>
                                    <button type="button" class="btn btn-danger" title="حذف" data-bs-toggle="tooltip" 
                                            onclick="confirmDeleteClass(<?= $class['id'] ?>, '<?= htmlspecialchars($class['class_name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-chalkboard fa-3x mb-2 d-block"></i>
                                هیچ کلاسی تعریف نشده است
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteClassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                <h5 id="deleteClassTitle"></h5>
                <p class="text-muted">بخش‌ها و داده‌های مرتبط نیز حذف خواهند شد.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteClassForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteClass(id, name) {
        document.getElementById('deleteClassTitle').textContent = 'آیا از حذف کلاس "' + name + '" اطمینان دارید؟';
        document.getElementById('deleteClassForm').action = '<?= url('/classes') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteClassModal')).show();
    }
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>