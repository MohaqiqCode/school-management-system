<?php
$title = 'مدیریت دانش‌آموزان';
$students = $students ?? [];
$classes = $classes ?? [];
$sections = $sections ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
$classFilter = $classFilter ?? '';
$sectionFilter = $sectionFilter ?? '';
$statusFilter = $statusFilter ?? '';

$content = function() use ($students, $classes, $sections, $currentPage, $totalPages, $total, $search, $classFilter, $sectionFilter, $statusFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-graduate me-2"></i>مدیریت دانش‌آموزان</h1>
    <?php if (hasPermission('create_students')): ?>
    <a href="<?= url('/students/create') ?>" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i>ثبت نام جدید</a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/students') ?>" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="جستجو بر اساس نام، کد یا ایمیل..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="class_id" onchange="this.form.submit()">
                    <option value="">همه کلاس‌ها</option>
                    <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= $classFilter == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="section_id">
                    <option value="">همه بخش‌ها</option>
                    <?php foreach ($sections as $sec): ?>
                    <option value="<?= $sec['id'] ?>" <?= $sectionFilter == $sec['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sec['section_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">همه</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>فعال</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>غیرفعال</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال فیلتر</button>
                <?php if ($search || $classFilter || $sectionFilter || $statusFilter !== ''): ?>
                <a href="<?= url('/students') ?>" class="btn btn-outline-secondary w-100 mt-1">پاک کردن</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>لیست دانش‌آموزان</h6>
        <span class="badge bg-primary rounded-pill"><?= number_format($total) ?> دانش‌آموز</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="studentsTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="8%">تصویر</th>
                        <th width="12%">کد</th>
                        <th width="20%">نام</th>
                        <th width="12%">کلاس</th>
                        <th width="10%">بخش</th>
                        <th width="12%">تاریخ ثبت نام</th>
                        <th width="7%">وضعیت</th>
                        <th width="14%">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $i => $student): ?>
                        <tr>
                            <td><?= ($currentPage - 1) * 25 + $i + 1 ?></td>
                            <td><img src="<?= $student['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle" width="38" height="38"></td>
                            <td><code><?= htmlspecialchars($student['student_code']) ?></code></td>
                            <td>
                                <a href="<?= url('/students/' . $student['id']) ?>" class="text-decoration-none fw-bold">
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </a>
                                <br><small class="text-muted"><?= htmlspecialchars($student['email'] ?? '') ?></small>
                            </td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($student['class_name'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($student['section_name'] ?? '-') ?></td>
                            <td><small><?= date('Y/m/d', strtotime($student['admission_date'] ?? '')) ?></small></td>
                            <td><?= $student['is_active'] ? '<span class="badge bg-success">فعال</span>' : '<span class="badge bg-danger">غیرفعال</span>' ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/students/' . $student['id']) ?>" class="btn btn-info" title="مشاهده" data-bs-toggle="tooltip"><i class="fas fa-eye"></i></a>
                                    <?php if (hasPermission('edit_students')): ?>
                                    <a href="<?= url('/students/' . $student['id'] . '/edit') ?>" class="btn btn-warning" title="ویرایش" data-bs-toggle="tooltip"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('promote_students')): ?>
                                    <a href="<?= url('/students/' . $student['id'] . '/promote') ?>" class="btn btn-success" title="ارتقاء" data-bs-toggle="tooltip"><i class="fas fa-level-up-alt"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('delete_students')): ?>
                                    <button type="button" class="btn btn-danger" title="حذف" onclick="confirmDeleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>')"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-user-graduate fa-3x mb-2 d-block"></i>دانش‌آموزی یافت نشد</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer"><nav><ul class="pagination justify-content-center mb-0"><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="page-item <?= $p == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&class_id=<?= $classFilter ?>&section_id=<?= $sectionFilter ?>&status=<?= $statusFilter ?>"><?= $p ?></a></li><?php endfor; ?></ul></nav></div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center py-4"><i class="fas fa-user-slash fa-3x text-danger mb-3"></i><h5 id="deleteStudentTitle"></h5></div><div class="modal-footer justify-content-center"><button class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><form id="deleteStudentForm" method="POST"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="btn btn-danger">حذف</button></form></div></div></div></div>

<script>
    function confirmDeleteStudent(id, name) { document.getElementById('deleteStudentTitle').textContent = 'آیا از حذف "' + name + '" اطمینان دارید؟'; document.getElementById('deleteStudentForm').action = '<?= url('/students') ?>/' + id; new bootstrap.Modal(document.getElementById('deleteStudentModal')).show(); }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>