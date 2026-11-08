<?php
$title = 'مدیریت برنامه هفتگی';
$timetables = $timetables ?? [];
$classes = $classes ?? [];
$teachers = $teachers ?? [];
$semesters = $semesters ?? [];
$selectedClass = $selectedClass ?? '';
$selectedSection = $selectedSection ?? '';
$selectedTeacher = $selectedTeacher ?? '';
$selectedSemester = $selectedSemester ?? '';

$content = function() use ($timetables, $classes, $teachers, $semesters, $selectedClass, $selectedSection, $selectedTeacher, $selectedSemester) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-alt me-2"></i>مدیریت برنامه هفتگی</h1>
    <?php if (hasPermission('create_timetable')): ?>
    <a href="<?= url('/timetable/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>ایجاد برنامه جدید</a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/timetable') ?>" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">کلاس</label>
                <select class="form-select form-select-sm" name="class_id" onchange="loadSections(this.value)">
                    <option value="">همه کلاس‌ها</option>
                    <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= $selectedClass == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">بخش</label>
                <select class="form-select form-select-sm" name="section_id" id="sectionSelect">
                    <option value="">همه بخش‌ها</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">معلم</label>
                <select class="form-select form-select-sm" name="teacher_id">
                    <option value="">همه معلمان</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $selectedTeacher == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">ترم</label>
                <select class="form-select form-select-sm" name="semester_id">
                    <option value="">همه ترم‌ها</option>
                    <?php foreach ($semesters as $sem): ?>
                    <option value="<?= $sem['id'] ?>" <?= $selectedSemester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['semester_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>فیلتر</button>
            </div>
        </form>
    </div>
</div>

<!-- Weekly View -->
<?php
$days = ['Saturday' => 'شنبه', 'Sunday' => 'یکشنبه', 'Monday' => 'دوشنبه', 'Tuesday' => 'سه‌شنبه', 
         'Wednesday' => 'چهارشنبه', 'Thursday' => 'پنج‌شنبه', 'Friday' => 'جمعه'];
$dayColors = ['Saturday' => '#e74a3b', 'Sunday' => '#3498db', 'Monday' => '#2ecc71', 'Tuesday' => '#f39c12', 
              'Wednesday' => '#9b59b6', 'Thursday' => '#1abc9c', 'Friday' => '#95a5a6'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-table me-2"></i>برنامه هفتگی</h6>
        <span class="badge bg-primary rounded-pill"><?= count($timetables ?? []) ?> برنامه</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="timetableGrid">
                <thead>
                    <tr class="text-center bg-light">
                        <th width="8%">زمان</th>
                        <?php foreach ($days as $dayEn => $dayFa): ?>
                        <th style="background-color: <?= $dayColors[$dayEn] ?>20; color: <?= $dayColors[$dayEn] ?>;">
                            <?= $dayFa ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $timeSlots = ['08:00-09:30', '09:30-11:00', '11:00-12:30', '13:00-14:30', '14:30-16:00', '16:00-17:30'];
                    foreach ($timeSlots as $slot):
                        list($start, $end) = explode('-', $slot);
                    ?>
                    <tr>
                        <td class="text-center align-middle bg-light small fw-bold"><?= $start ?><br><?= $end ?></td>
                        <?php foreach (array_keys($days) as $dayEn): ?>
                        <td class="p-1 align-middle text-center" style="min-height: 60px;">
                            <?php
                            $found = false;
                            foreach ($timetables as $tt) {
                                if ($tt['day_of_week'] === $dayEn && $tt['start_time'] === $start . ':00') {
                                    $found = true;
                                    ?>
                                    <div class="bg-primary bg-opacity-10 rounded p-1 small border-start border-4 border-primary">
                                        <strong><?= htmlspecialchars($tt['subject_name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($tt['section_name'] ?? '') ?> | <?= htmlspecialchars($tt['class_name'] ?? '') ?></small>
                                        <br><small><i class="fas fa-user me-1"></i><?= htmlspecialchars($tt['teacher_name'] ?? '') ?></small>
                                        <?php if (!empty($tt['room_number'])): ?>
                                        <br><small><i class="fas fa-door-open me-1"></i><?= htmlspecialchars($tt['room_number']) ?></small>
                                        <?php endif; ?>
                                        <div class="mt-1">
                                            <a href="<?= url('/timetable/' . $tt['id'] . '/edit') ?>" class="btn btn-sm btn-outline-warning py-0 px-1" title="ویرایش"><i class="fas fa-edit fa-xs"></i></a>
                                        </div>
                                    </div>
                                    <?php break;
                                }
                            }
                            if (!$found) echo '<span class="text-muted small">-</span>';
                            ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- List View (Mobile Friendly) -->
<div class="d-md-none">
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold">لیست برنامه‌ها</h6></div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if (!empty($timetables)): ?>
                    <?php foreach ($timetables as $tt): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($tt['subject_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($tt['class_name'] ?? '') ?> | <?= htmlspecialchars($tt['section_name'] ?? '') ?></small>
                            </div>
                            <span class="badge bg-primary"><?= $tt['start_time'] ?> - <?= $tt['end_time'] ?></span>
                        </div>
                        <div class="small mt-1">
                            <span class="badge" style="background-color:<?= $dayColors[$tt['day_of_week']] ?? '#ccc' ?>; color:white;"><?= $days[$tt['day_of_week']] ?? $tt['day_of_week'] ?></span>
                            <i class="fas fa-user ms-2 me-1"></i><?= htmlspecialchars($tt['teacher_name'] ?? '') ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-center text-muted py-4">برنامه‌ای یافت نشد</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function loadSections(classId) {
        const sectionSelect = document.getElementById('sectionSelect');
        sectionSelect.innerHTML = '<option value="">همه بخش‌ها</option>';
        if (classId) {
            fetch('<?= url('/api/sections') ?>/' + classId)
                .then(r => r.json())
                .then(d => {
                    if (d.success && d.data) {
                        d.data.forEach(s => {
                            sectionSelect.innerHTML += `<option value="${s.id}" <?= $selectedSection ?>==${s.id}?'selected':''>${s.section_name}</option>`;
                        });
                    }
                });
        }
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>