<?php
$title = 'ویرایش نقش';
$role = $role ?? [];
$groupedPermissions = $groupedPermissions ?? [];
$old = $_SESSION['old'] ?? $role;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$moduleLabels = [
    'dashboard' => ['label' => 'داشبورد', 'icon' => 'fas fa-tachometer-alt'],
    'branches' => ['label' => 'شعب', 'icon' => 'fas fa-building'],
    'users' => ['label' => 'کاربران', 'icon' => 'fas fa-users'],
    'roles' => ['label' => 'نقش‌ها', 'icon' => 'fas fa-user-shield'],
    'departments' => ['label' => 'دپارتمان‌ها', 'icon' => 'fas fa-sitemap'],
    'academic' => ['label' => 'آموزشی', 'icon' => 'fas fa-graduation-cap'],
    'classes' => ['label' => 'کلاس‌ها', 'icon' => 'fas fa-chalkboard'],
    'subjects' => ['label' => 'دروس', 'icon' => 'fas fa-book'],
    'timetable' => ['label' => 'برنامه هفتگی', 'icon' => 'fas fa-calendar-alt'],
    'students' => ['label' => 'دانش‌آموزان', 'icon' => 'fas fa-user-graduate'],
    'parents' => ['label' => 'والدین', 'icon' => 'fas fa-user-friends'],
    'teachers' => ['label' => 'معلمان', 'icon' => 'fas fa-chalkboard-teacher'],
    'employees' => ['label' => 'کارمندان', 'icon' => 'fas fa-briefcase'],
    'attendance' => ['label' => 'حضور و غیاب', 'icon' => 'fas fa-calendar-check'],
    'examinations' => ['label' => 'آزمون‌ها', 'icon' => 'fas fa-file-alt'],
    'finance' => ['label' => 'مالی', 'icon' => 'fas fa-money-bill-wave'],
    'payroll' => ['label' => 'حقوق', 'icon' => 'fas fa-receipt'],
    'library' => ['label' => 'کتابخانه', 'icon' => 'fas fa-book-open'],
    'transport' => ['label' => 'حمل و نقل', 'icon' => 'fas fa-bus'],
    'inventory' => ['label' => 'انبار', 'icon' => 'fas fa-boxes'],
    'notifications' => ['label' => 'اعلان‌ها', 'icon' => 'fas fa-bell'],
    'reports' => ['label' => 'گزارش‌ها', 'icon' => 'fas fa-chart-bar'],
    'audit' => ['label' => 'ممیزی', 'icon' => 'fas fa-history'],
    'settings' => ['label' => 'تنظیمات', 'icon' => 'fas fa-cog'],
];

$content = function() use ($role, $groupedPermissions, $moduleLabels, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش نقش</h1>
        <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($role['display_name'] ?? '') ?></p>
    </div>
    <div>
        <?php if (($role['can_be_deleted'] ?? true) && ($role['user_count'] ?? 0) == 0): ?>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $role['id'] ?>, '<?= htmlspecialchars($role['display_name']) ?>')">
            <i class="fas fa-trash me-1"></i>حذف نقش
        </button>
        <?php endif; ?>
        <a href="<?= url('/roles') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<form action="<?= url('/roles/' . ($role['id'] ?? '')) ?>" method="POST" id="roleForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <input type="hidden" name="_method" value="PUT">

    <div class="row">
        <!-- Main Info -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات نقش</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">نام سیستمی</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($role['name'] ?? '') ?>" disabled dir="ltr">
                        <small class="text-muted">نام سیستمی قابل تغییر نیست</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">نام نمایشی <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errors['display_name']) ? 'is-invalid' : '' ?>" 
                               name="display_name" value="<?= htmlspecialchars($old['display_name'] ?? $role['display_name'] ?? '') ?>" required>
                        <?php if (isset($errors['display_name'])): ?><div class="invalid-feedback"><?= $errors['display_name'] ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? $role['description'] ?? '') ?></textarea>
                    </div>
                    <?php if ($role['can_be_modified'] ?? true): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رنگ نمایش</label>
                        <input type="color" class="form-control form-control-color w-100" name="color" 
                               value="<?= htmlspecialchars($old['color'] ?? $role['color'] ?? '#4e73df') ?>" style="height:45px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">آیکون</label>
                        <select class="form-select" name="icon" id="iconSelect" onchange="updateIconPreview()">
                            <?php
                            $icons = ['fas fa-user','fas fa-user-tie','fas fa-user-shield','fas fa-user-graduate','fas fa-user-cog',
                                      'fas fa-chalkboard-teacher','fas fa-briefcase','fas fa-crown','fas fa-shield-alt',
                                      'fas fa-star','fas fa-heart','fas fa-gem'];
                            foreach ($icons as $ic):
                            ?>
                            <option value="<?= $ic ?>" <?= ($old['icon'] ?? $role['icon'] ?? '') === $ic ? 'selected' : '' ?>><?= $ic ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-2 text-center"><i id="iconPreview" class="<?= htmlspecialchars($old['icon'] ?? $role['icon'] ?? 'fas fa-user') ?> fa-2x" style="color:<?= htmlspecialchars($old['color'] ?? $role['color'] ?? '#4e73df') ?>"></i></div>
                    </div>
                    <?php endif; ?>

                    <!-- Role Info -->
                    <div class="alert alert-info small">
                        <strong>نوع نقش:</strong> <?= ($role['is_system_role'] ?? false) ? 'سیستمی' : 'سفارشی' ?><br>
                        <strong>تعداد کاربران:</strong> <?= $role['user_count'] ?? 0 ?> کاربر<br>
                        <strong>تاریخ ایجاد:</strong> <?= date('Y/m/d', strtotime($role['created_at'] ?? 'now')) ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()"><i class="fas fa-check-square me-1"></i>انتخاب همه</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()"><i class="fas fa-square me-1"></i>حذف همه</button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="resetToDefault()"><i class="fas fa-undo me-1"></i>بازنشانی به پیش‌فرض</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-key me-2"></i>مجوزهای دسترسی</h6>
                    <span class="badge bg-primary" id="selectedCount">0 انتخاب شده</span>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="permissionSearch" placeholder="جستجوی مجوز...">
                    </div>

                    <div class="accordion" id="permissionsAccordion">
                        <?php foreach ($groupedPermissions as $module => $permissions): 
                            $modInfo = $moduleLabels[$module] ?? ['label' => $module, 'icon' => 'fas fa-circle'];
                            $moduleId = 'module-' . $module;
                        ?>
                        <div class="accordion-item permission-module" data-module="<?= htmlspecialchars($module) ?>">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $moduleId ?>">
                                    <i class="<?= $modInfo['icon'] ?> me-2"></i>
                                    <strong><?= $modInfo['label'] ?></strong>
                                    <span class="badge bg-secondary ms-2"><?= count($permissions) ?></span>
                                    <button type="button" class="btn btn-link btn-sm ms-auto text-decoration-none" onclick="event.stopPropagation(); toggleModule('<?= $module ?>')">
                                        <small>انتخاب همه</small>
                                    </button>
                                </button>
                            </h2>
                            <div id="<?= $moduleId ?>" class="accordion-collapse collapse" data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <?php foreach ($permissions as $perm): ?>
                                        <div class="col-md-6 mb-2 permission-item" data-search="<?= htmlspecialchars(strtolower($perm['display_name'])) ?>">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                                       name="permissions[]" value="<?= $perm['id'] ?>" 
                                                       id="perm-<?= $perm['id'] ?>" 
                                                       data-module="<?= htmlspecialchars($module) ?>"
                                                       <?= $perm['assigned'] ? 'checked' : '' ?>
                                                       onchange="updateCount()">
                                                <label class="form-check-label" for="perm-<?= $perm['id'] ?>">
                                                    <?= htmlspecialchars($perm['display_name']) ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($perm['name']) ?></small>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0 small"><i class="fas fa-info-circle me-1"></i>تغییرات پس از ذخیره بلافاصله برای همه کاربران این نقش اعمال می‌شود.</p>
                <div>
                    <a href="<?= url('/roles') ?>" class="btn btn-secondary px-4">انصراف</a>
                    <button type="submit" class="btn btn-primary px-4 ms-2"><i class="fas fa-save me-1"></i>ذخیره تغییرات</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let originalState = {};
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => { originalState[cb.value] = cb.checked; });
        updateCount();
    });

    function updateCount() {
        const count = document.querySelectorAll('.permission-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = count + ' انتخاب شده';
    }

    function selectAll() { document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true); updateCount(); }
    function deselectAll() { document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false); updateCount(); }
    function resetToDefault() { document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = originalState[cb.value] || false); updateCount(); }

    function toggleModule(module) {
        const checkboxes = document.querySelectorAll('.permission-checkbox[data-module="' + module + '"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        updateCount();
    }

    function updateIconPreview() {
        const icon = document.getElementById('iconSelect').value;
        const color = document.querySelector('input[name="color"]').value;
        document.getElementById('iconPreview').className = icon + ' fa-2x';
        document.getElementById('iconPreview').style.color = color;
    }

    document.querySelector('input[name="color"]')?.addEventListener('input', updateIconPreview);

    document.getElementById('permissionSearch').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.permission-item').forEach(item => {
            item.style.display = item.dataset.search.includes(query) ? '' : 'none';
        });
        if (query.length > 0) document.querySelectorAll('.accordion-collapse').forEach(c => c.classList.add('show'));
    });

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'حذف نقش',
            text: 'آیا از حذف نقش "' + name + '" اطمینان دارید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'حذف',
            cancelButtonText: 'انصراف',
            confirmButtonColor: '#e74a3b'
        }).then(r => {
            if (r.isConfirmed) {
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = '<?= url('/roles') ?>/' + id;
                f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">';
                document.body.appendChild(f);
                f.submit();
            }
        });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>