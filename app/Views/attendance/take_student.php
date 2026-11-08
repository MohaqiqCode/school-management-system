<?php
$title = 'ثبت حضور و غیاب';
$students = $students ?? [];
$class = $class ?? [];
$section = $section ?? [];
$date = $date ?? date('Y-m-d');

$content = function() use ($students, $class, $section, $date) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-clipboard-check me-2"></i>ثبت حضور و غیاب</h1><a href="<?= url('/attendance/students') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<div class="alert alert-info"><strong>کلاس:</strong> <?=htmlspecialchars($class['class_name']??'')?> | <strong>بخش:</strong> <?=htmlspecialchars($section['section_name']??'')?> | <strong>تاریخ:</strong> <?=date('Y/m/d',strtotime($date))?></div>

<form action="<?= url('/attendance/students') ?>" method="POST" id="attendanceForm"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="class_id" value="<?=$class['id']??''?>"><input type="hidden" name="section_id" value="<?=$section['id']??''?>"><input type="hidden" name="attendance_date" value="<?=$date?>">

<div class="card shadow mb-4"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold">لیست دانش‌آموزان (<?=count($students??[])?>)</h6><div><button type="button" class="btn btn-sm btn-outline-success me-1" onclick="setAll('present')">همه حاضر</button><button type="button" class="btn btn-sm btn-outline-danger" onclick="setAll('absent')">همه غایب</button></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>نام</th><th>کد</th><th>وضعیت</th><th>توضیحات</th></tr></thead><tbody>
<?php if(!empty($students)): ?>
    <?php foreach($students as $i=>$s): ?>
    <tr><td><?=$i+1?></td><td><img src="<?=$s['profile_photo']??asset('images/default-avatar.png')?>" class="rounded-circle me-2" width="32" height="32"><?=htmlspecialchars($s['first_name'].' '.$s['last_name'])?></td><td><code><?=htmlspecialchars($s['student_code'])?></code></td><td>
        <select class="form-select form-select-sm status-select" name="attendance[<?=$s['id']?>]" style="width:120px">
            <option value="present" <?=($s['current_status']??'')==='present'?'selected':''?>>حاضر</option><option value="absent" <?=($s['current_status']??'')==='absent'?'selected':''?>>غایب</option><option value="late" <?=($s['current_status']??'')==='late'?'selected':''?>>تأخیر</option><option value="half_day" <?=($s['current_status']??'')==='half_day'?'selected':''?>>نیمه وقت</option><option value="excused" <?=($s['current_status']??'')==='excused'?'selected':''?>>موجه</option>
        </select></td><td><input type="text" class="form-control form-control-sm" name="remarks[<?=$s['id']?>]" placeholder="توضیحات..."></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="5" class="text-center py-4">دانش‌آموزی یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div><div class="card-footer"><button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>ذخیره حضور و غیاب</button></div></div>
</form>

<script>function setAll(status){document.querySelectorAll('.status-select').forEach(s=>s.value=status)}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>