<?php
$title = 'ثبت گروهی حضور و غیاب';
$classes = $classes ?? [];
$selectedClass = $selectedClass ?? '';
$selectedSection = $selectedSection ?? '';
$date = $date ?? date('Y-m-d');

$content = function() use ($classes, $selectedClass, $selectedSection, $date) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-check-double me-2"></i>ثبت گروهی حضور و غیاب</h1><a href="<?=url('/attendance/students')?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<div class="card shadow mb-4"><div class="card-body"><form action="<?=url('/attendance/students/take')?>" method="GET" class="row g-3"><div class="col-md-3"><select class="form-select" name="class_id" id="classSelect" onchange="loadSections(this.value)"><option value="">انتخاب کلاس</option><?php foreach($classes as $cl):?><option value="<?=$cl['id']?>" <?=$selectedClass==$cl['id']?'selected':''?>><?=htmlspecialchars($cl['class_name'])?></option><?php endforeach;?></select></div><div class="col-md-3"><select class="form-select" name="section_id" id="sectionSelect"><option value="">انتخاب بخش</option></select></div><div class="col-md-3"><input type="date" class="form-control" name="date" value="<?=$date?>"></div><div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>مشاهده دانش‌آموزان</button></div></form></div></div>

<?php if($selectedClass && $selectedSection): ?>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>برای ثبت سریع، وضعیت پیش‌فرض را انتخاب و روی دکمه "اعمال به همه" کلیک کنید. سپس تغییرات را ذخیره نمایید.</div>
<div class="card shadow mb-4"><div class="card-header bg-light"><div class="row align-items-center"><div class="col-md-4"><strong>وضعیت پیش‌فرض:</strong> <select class="form-select form-select-sm d-inline-block w-auto ms-2" id="bulkStatus"><option value="present">حاضر</option><option value="absent">غایب</option><option value="late">تأخیر</option></select></div><div class="col-md-4"><button type="button" class="btn btn-outline-primary btn-sm" onclick="applyBulk()"><i class="fas fa-check-double me-1"></i>اعمال به همه</button></div><div class="col-md-4 text-end"><button type="button" class="btn btn-success btn-sm" onclick="submitBulk()"><i class="fas fa-save me-1"></i>ذخیره همه</button></div></div></div></div>
<?php endif; ?>

<script>function loadSections(cid){const s=document.getElementById('sectionSelect');s.innerHTML='<option value="">انتخاب بخش...</option>';if(cid){fetch('<?=url('/api/sections')?>/'+cid).then(r=>r.json()).then(d=>{if(d.success&&d.data)d.data.forEach(sec=>s.innerHTML+=`<option value="${sec.id}" <?=$selectedSection?>==sec.id?'selected':''>${sec.section_name}</option>`)})}}function applyBulk(){const st=document.getElementById('bulkStatus').value;document.querySelectorAll('.status-select').forEach(s=>s.value=st)}document.addEventListener('DOMContentLoaded',function(){const cid=document.getElementById('classSelect').value;if(cid)loadSections(cid)});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>