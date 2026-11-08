<?php
$title = 'تنظیمات شعبه';
$branch = $branch ?? [];
$gradingScales = $gradingScales ?? [];
$feeCategories = $feeCategories ?? [];

$content = function() use ($branch, $gradingScales, $feeCategories) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cog me-2"></i>تنظیمات شعبه</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 mt-2"><li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">داشبورد</a></li><li class="breadcrumb-item"><a href="<?= url('/branches') ?>">شعب</a></li><li class="breadcrumb-item active">تنظیمات <?= htmlspecialchars($branch['name'] ?? '') ?></li></ol></nav>
    </div>
    <div>
        <a href="<?= url('/branches/' . ($branch['id'] ?? '')) ?>" class="btn btn-info"><i class="fas fa-eye me-1"></i> مشاهده شعبه</a>
        <a href="<?= url('/branches') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i> بازگشت</a>
    </div>
</div>

<form action="<?= url('/branches/' . ($branch['id'] ?? '') . '/settings') ?>" method="POST" id="settingsForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <input type="hidden" name="_method" value="PUT">

    <!-- Grading Scales -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-star me-2"></i>مقیاس‌های نمره‌دهی</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addGradeRow()">
                <i class="fas fa-plus me-1"></i> افزودن ردیف
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">تعیین کنید هر بازه درصدی معادل چه نمره‌ای است.</p>
            <div class="table-responsive">
                <table class="table table-bordered" id="gradesTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">نام نمره</th>
                            <th width="15%">حداقل درصد</th>
                            <th width="15%">حداکثر درصد</th>
                            <th width="15%">معدل (GPA)</th>
                            <th width="25%">توضیحات</th>
                            <th width="10%">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($gradingScales)): ?>
                            <?php foreach ($gradingScales as $i => $grade): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><input type="text" class="form-control form-control-sm" name="grades[<?= $i ?>][grade_name]" value="<?= htmlspecialchars($grade['grade_name']) ?>" required></td>
                                <td><input type="number" class="form-control form-control-sm" name="grades[<?= $i ?>][min_percentage]" value="<?= $grade['min_percentage'] ?>" step="0.01" min="0" max="100" required></td>
                                <td><input type="number" class="form-control form-control-sm" name="grades[<?= $i ?>][max_percentage]" value="<?= $grade['max_percentage'] ?>" step="0.01" min="0" max="100" required></td>
                                <td><input type="number" class="form-control form-control-sm" name="grades[<?= $i ?>][grade_point]" value="<?= $grade['grade_point'] ?>" step="0.1" min="0" max="4"></td>
                                <td><input type="text" class="form-control form-control-sm" name="grades[<?= $i ?>][description]" value="<?= htmlspecialchars($grade['description'] ?? '') ?>"></td>
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">مقیاسی تعریف نشده است. روی "افزودن ردیف" کلیک کنید.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Fee Categories -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-success"><i class="fas fa-tags me-2"></i>دسته‌بندی‌های شهریه</h6>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="addFeeCategoryRow()">
                <i class="fas fa-plus me-1"></i> افزودن ردیف
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="feeCategoriesTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">نام دسته‌بندی</th>
                            <th width="10%">دوره‌ای</th>
                            <th width="15%">تناوب</th>
                            <th width="40%">توضیحات</th>
                            <th width="10%">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($feeCategories)): ?>
                            <?php foreach ($feeCategories as $i => $fc): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><input type="text" class="form-control form-control-sm" name="fee_categories[<?= $i ?>][category_name]" value="<?= htmlspecialchars($fc['category_name']) ?>" required></td>
                                <td class="text-center"><input type="checkbox" class="form-check-input" name="fee_categories[<?= $i ?>][is_recurring]" value="1" <?= ($fc['is_recurring'] ?? 1) ? 'checked' : '' ?>></td>
                                <td>
                                    <select class="form-select form-select-sm" name="fee_categories[<?= $i ?>][frequency]">
                                        <option value="monthly" <?= ($fc['frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>ماهانه</option>
                                        <option value="quarterly" <?= ($fc['frequency'] ?? '') === 'quarterly' ? 'selected' : '' ?>>سه ماهه</option>
                                        <option value="half_yearly" <?= ($fc['frequency'] ?? '') === 'half_yearly' ? 'selected' : '' ?>>شش ماهه</option>
                                        <option value="yearly" <?= ($fc['frequency'] ?? '') === 'yearly' ? 'selected' : '' ?>>سالانه</option>
                                        <option value="one_time" <?= ($fc['frequency'] ?? '') === 'one_time' ? 'selected' : '' ?>>یکبار</option>
                                    </select>
                                </td>
                                <td><input type="text" class="form-control form-control-sm" name="fee_categories[<?= $i ?>][description]" value="<?= htmlspecialchars($fc['description'] ?? '') ?>"></td>
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">دسته‌بندی تعریف نشده است.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0 small"><i class="fas fa-info-circle me-1"></i>تغییرات پس از ذخیره بلافاصله اعمال می‌شوند.</p>
                <div>
                    <a href="<?= url('/branches') ?>" class="btn btn-secondary px-4">انصراف</a>
                    <button type="submit" class="btn btn-primary px-4 ms-2"><i class="fas fa-save me-1"></i> ذخیره تنظیمات</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let gradeIndex = <?= count($gradingScales ?? []) ?>;
    let feeCatIndex = <?= count($feeCategories ?? []) ?>;

    function addGradeRow() {
        const tbody = document.querySelector('#gradesTable tbody');
        if (tbody.querySelector('td[colspan]')) tbody.innerHTML = '';
        const row = `
            <tr>
                <td>${gradeIndex + 1}</td>
                <td><input type="text" class="form-control form-control-sm" name="grades[${gradeIndex}][grade_name]" required></td>
                <td><input type="number" class="form-control form-control-sm" name="grades[${gradeIndex}][min_percentage]" step="0.01" min="0" max="100" required></td>
                <td><input type="number" class="form-control form-control-sm" name="grades[${gradeIndex}][max_percentage]" step="0.01" min="0" max="100" required></td>
                <td><input type="number" class="form-control form-control-sm" name="grades[${gradeIndex}][grade_point]" step="0.1" min="0" max="4" value="0"></td>
                <td><input type="text" class="form-control form-control-sm" name="grades[${gradeIndex}][description]"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
            </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
        gradeIndex++;
    }

    function addFeeCategoryRow() {
        const tbody = document.querySelector('#feeCategoriesTable tbody');
        if (tbody.querySelector('td[colspan]')) tbody.innerHTML = '';
        const row = `
            <tr>
                <td>${feeCatIndex + 1}</td>
                <td><input type="text" class="form-control form-control-sm" name="fee_categories[${feeCatIndex}][category_name]" required></td>
                <td class="text-center"><input type="checkbox" class="form-check-input" name="fee_categories[${feeCatIndex}][is_recurring]" value="1" checked></td>
                <td><select class="form-select form-select-sm" name="fee_categories[${feeCatIndex}][frequency]">
                    <option value="monthly">ماهانه</option><option value="quarterly">سه ماهه</option><option value="half_yearly">شش ماهه</option><option value="yearly">سالانه</option><option value="one_time">یکبار</option></select></td>
                <td><input type="text" class="form-control form-control-sm" name="fee_categories[${feeCatIndex}][description]"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
            </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
        feeCatIndex++;
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>