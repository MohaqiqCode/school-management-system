<?php
$title = 'حضور و غیاب معلمان';
$teachers = $teachers ?? [];
$date = $date ?? date('Y-m-d');

$content = function() use ($teachers, $date) {
    ob_start();
    $statusColors=['present'=>'success','absent'=>'danger','late'=>'warning','half_day'=>'info','on_leave'=>'secondary'];
    $statusTexts=['present'=>'حاضر','absent'=>'غایب','late'=>'تأخیر','half_day'=>'نیمه وقت','on_leave'=>'مرخصی'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-calendar-check me-2"></i>حضور و غیاب معلمان</h1></div>

<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/attendance/teachers') ?>" method="GET" class="row g-3"><div class="col-md-4"><input type="date" class="form-control" name="date" value="<?=$date?>"></div><div class="col-md-4"><button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>مشاهده</button></div></form></div></div>

<div class="card shadow mb-4"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold">وضعیت حضور - <?=date('Y/m/d',strtotime($date))?></h6><button class="btn btn-sm btn-success" onclick="markAll()"><i class="fas fa-check-double me-1"></i>ثبت گروهی</button></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0" id="teacherTable"><thead><tr><th>#</th><th>معلم</th><th>کد</th><th>وضعیت</th><th>ساعت ورود</th><th>ساعت خروج</th><th>توضیحات</th><th>عملیات</th></tr></thead><tbody>
<?php if(!empty($teachers)): ?>
    <?php foreach($teachers as $i=>$t): ?>
    <tr id="row-<?=$t['id']?>"><td><?=$i+1?></td><td><strong><?=htmlspecialchars($t['first_name'].' '.$t['last_name'])?></strong></td><td><code><?=htmlspecialchars($t['teacher_code'])?></code></td><td><span class="badge bg-<?=$statusColors[$t['attendance_status']]??'light'?>" id="status-<?=$t['id']?>"><?=$statusTexts[$t['attendance_status']]??'ثبت نشده'?></span></td><td id="in-<?=$t['id']?>"><?=$t['check_in_time']??'-'?></td><td id="out-<?=$t['id']?>"><?=$t['check_out_time']??'-'?></td><td id="remarks-<?=$t['id']?>"><?=htmlspecialchars($t['remarks']??'-')?></td><td><button class="btn btn-sm btn-outline-primary" onclick="markTeacher(<?=$t['id']?>)"><i class="fas fa-edit"></i></button></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="8" class="text-center py-4">معلمی یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<script>
function markTeacher(id){Swal.fire({title:'ثبت حضور',html:`<select id="swal-status" class="form-select mb-2"><option value="present">حاضر</option><option value="absent">غایب</option><option value="late">تأخیر</option><option value="on_leave">مرخصی</option></select><input id="swal-checkin" class="form-control mb-2" type="time" placeholder="ساعت ورود"><input id="swal-checkout" class="form-control mb-2" type="time" placeholder="ساعت خروج"><input id="swal-remarks" class="form-control" placeholder="توضیحات">`,showCancelButton:true,confirmButtonText:'ذخیره',cancelButtonText:'انصراف'}).then(r=>{if(r.isConfirmed){const d={teacher_id:id,status:document.getElementById('swal-status').value,check_in_time:document.getElementById('swal-checkin').value,check_out_time:document.getElementById('swal-checkout').value,remarks:document.getElementById('swal-remarks').value,date:'<?=$date?>',csrf_token:'<?=$_SESSION['csrf_token']??''?>'};fetch('<?=url('/attendance/teachers')?>',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'},body:JSON.stringify(d)}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else toastr.error(d.message)})}})}
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>