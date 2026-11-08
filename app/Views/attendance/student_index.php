<?php
$title = 'حضور و غیاب دانش‌آموزان';
$classes = $classes ?? [];
$attendanceData = $attendanceData ?? [];
$selectedClass = $selectedClass ?? '';
$selectedSection = $selectedSection ?? '';
$selectedDate = $selectedDate ?? date('Y-m-d');

$content = function() use ($classes, $attendanceData, $selectedClass, $selectedSection, $selectedDate) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-calendar-check me-2"></i>حضور و غیاب دانش‌آموزان</h1><?php if(hasPermission('mark_attendance')): ?><a href="<?= url('/attendance/students/take') ?>" class="btn btn-success"><i class="fas fa-clipboard-check me-1"></i>ثبت حضور و غیاب</a><?php endif; ?></div>

<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/attendance/students') ?>" method="GET" class="row g-3"><div class="col-md-3"><select class="form-select" name="class_id" id="classSelect" onchange="loadSections(this.value)"><option value="">انتخاب کلاس...</option><?php foreach($classes as $cl): ?><option value="<?=$cl['id']?>" <?=$selectedClass==$cl['id']?'selected':''?>><?=htmlspecialchars($cl['class_name'])?></option><?php endforeach; ?></select></div><div class="col-md-3"><select class="form-select" name="section_id" id="sectionSelect"><option value="">انتخاب بخش...</option></select></div><div class="col-md-3"><input type="date" class="form-control" name="date" value="<?=$selectedDate?>"></div><div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>مشاهده</button></div></form></div></div>

<?php if($selectedClass && $selectedSection): ?>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">وضعیت حضور - <?= date('Y/m/d', strtotime($selectedDate)) ?></h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>دانش‌آموز</th><th>کد</th><th>وضعیت</th><th>ساعت ورود</th><th>توضیحات</th></tr></thead><tbody>
<?php if(!empty($attendanceData)): ?>
    <?php $statusColors=['present'=>'success','absent'=>'danger','late'=>'warning','half_day'=>'info','excused'=>'secondary']; $statusTexts=['present'=>'حاضر','absent'=>'غایب','late'=>'تأخیر','half_day'=>'نیمه وقت','excused'=>'موجه']; ?>
    <?php foreach($attendanceData as $i=>$att): ?>
    <tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($att['first_name'].' '.$att['last_name'])?></strong></td><td><code><?=htmlspecialchars($att['student_code'])?></code></td><td><span class="badge bg-<?=$statusColors[$att['status']]??'light'?>"><?=$statusTexts[$att['status']]??$att['status']?></span></td><td><?=$att['check_in_time']??'-'?></td><td><?=htmlspecialchars($att['remarks']??'-')?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="6" class="text-center py-4 text-muted">حضور و غیاب برای این تاریخ ثبت نشده است</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php endif; ?>

<script>function loadSections(cid){const s=document.getElementById('sectionSelect');s.innerHTML='<option value="">انتخاب بخش...</option>';if(cid){fetch('<?=url('/api/sections')?>/'+cid).then(r=>r.json()).then(d=>{if(d.success&&d.data)d.data.forEach(sec=>s.innerHTML+=`<option value="${sec.id}" <?=$selectedSection?>==sec.id?'selected':''>${sec.section_name}</option>`)})}}document.addEventListener('DOMContentLoaded',function(){const cid=document.getElementById('classSelect').value;if(cid)loadSections(cid)});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>