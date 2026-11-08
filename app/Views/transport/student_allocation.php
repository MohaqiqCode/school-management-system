<?php
$title = 'تخصیص حمل و نقل دانش‌آموزان';
$allocations = $allocations ?? [];
$routes = $routes ?? [];
$selectedRoute = $selectedRoute ?? '';

$content = function() use ($allocations, $routes, $selectedRoute) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-user-check me-2"></i>تخصیص حمل و نقل</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal"><i class="fas fa-plus me-1"></i>تخصیص جدید</button>
</div>

<!-- Filter -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-8"><select class="form-select" name="route_id" onchange="this.form.submit()"><option value="">همه مسیرها</option><?php foreach($routes as $r): ?><option value="<?=$r['id']?>" <?=$selectedRoute==$r['id']?'selected':''?>><?=htmlspecialchars($r['route_name'])?> (<?=$r['route_code']?>)</option><?php endforeach; ?></select></div><div class="col-md-4"><a href="<?=url('/transport/student-allocation')?>" class="btn btn-outline-secondary w-100">پاک کردن</a></div></form></div></div>

<!-- Allocations Table -->
<div class="card shadow mb-4"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold">لیست تخصیص‌ها (<?=count($allocations??[])?>)</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>دانش‌آموز</th><th>کد</th><th>کلاس</th><th>مسیر</th><th>ایستگاه</th><th>شهریه</th><th>تاریخ تخصیص</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
<?php if (!empty($allocations)): ?>
    <?php foreach ($allocations as $i => $a): ?>
    <tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars(($a['first_name']??'').' '.($a['last_name']??''))?></strong></td><td><code><?=htmlspecialchars($a['student_code']??'')?></code></td><td><?=htmlspecialchars($a['class_name']??'')?></td><td><?=htmlspecialchars($a['route_name']??'')?></td><td><?=htmlspecialchars($a['stop_name']??'تعیین نشده')?></td><td><?=number_format($a['monthly_fee']??0)?> ت</td><td><?=date('Y/m/d',strtotime($a['allocation_date']??''))?></td><td><span class="badge bg-<?=$a['is_active']?'success':'danger'?>"><?=$a['is_active']?'فعال':'غیرفعال'?></span></td><td><?php if($a['is_active']): ?><button class="btn btn-sm btn-danger" onclick="deallocate(<?=$a['id']?>)"><i class="fas fa-times me-1"></i>لغو</button><?php endif; ?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="10" class="text-center py-4">تخصیصی ثبت نشده</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<!-- Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="<?=url('/transport/allocate-student')?>" method="POST" id="allocateForm"><div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>تخصیص جدید</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><div class="mb-3"><label class="form-label">دانش‌آموز</label><input type="text" class="form-control" name="student_id" placeholder="کد دانش‌آموزی..." required></div><div class="mb-3"><label class="form-label">مسیر</label><select class="form-select" name="route_id" required><option value="">انتخاب...</option><?php foreach($routes as $r): ?><option value="<?=$r['id']?>"><?=htmlspecialchars($r['route_name'])?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">ایستگاه</label><select class="form-select" name="stop_id" id="stopSelect"><option value="">انتخاب...</option></select></div><div class="mb-3"><label class="form-label">شهریه ماهانه</label><input type="number" class="form-control" name="monthly_fee" value="0"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>ذخیره</button></div></form></div></div></div>

<script>
document.querySelector('[name="route_id"]').addEventListener('change', function(){const sid=this.value;fetch('<?=url('/api/route-stops')?>/'+sid).then(r=>r.json()).then(d=>{const s=document.getElementById('stopSelect');s.innerHTML='<option value="">انتخاب...</option>';if(d.success&&d.data)d.data.forEach(st=>s.innerHTML+=`<option value="${st.id}">${st.stop_name}</option>`)})});
function deallocate(id){Swal.fire({title:'لغو تخصیص',icon:'question',showCancelButton:true,confirmButtonText:'بله',cancelButtonText:'خیر'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/transport/deallocate')?>/'+id,{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else toastr.error(d.message)})}})}
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>