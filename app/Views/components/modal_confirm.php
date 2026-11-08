<?php
/**
 * Modal Confirm Component
 * 
 * Usage:
 *   echo modal_confirm('deleteModal', 'حذف', 'آیا اطمینان دارید؟', 'danger');
 *   echo modal_confirm_button('deleteBtn', 'حذف', 'آیا اطمینان دارید؟', 'danger', '/users/'.$id, 'DELETE');
 */
function modal_confirm($id, $title, $message, $color = 'danger', $options = []) {
    $confirmText = $options['confirmText'] ?? 'تأیید';
    $cancelText = $options['cancelText'] ?? 'انصراف';
    $icon = $options['icon'] ?? 'fa-exclamation-triangle';
    $size = $options['size'] ?? ''; // modal-sm, modal-lg
    ob_start();
?>
<div class="modal fade" id="<?=$id?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered <?=$size?>"><div class="modal-content border-0 shadow"><div class="modal-header bg-<?=$color?> text-white"><h5 class="modal-title"><i class="fas <?=$icon?> me-2"></i><?=$title?></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center py-4"><i class="fas <?=$icon?> fa-3x text-<?=$color?> mb-3"></i><h5><?=$message?></h5></div><div class="modal-footer justify-content-center"><button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><?=$cancelText?></button><button type="button" class="btn btn-<?=$color?> px-4" id="<?=$id?>Btn"><?=$confirmText?></button></div></div></div></div>
<?php return ob_get_clean(); }

function modal_confirm_button($id, $title, $message, $color = 'danger', $actionUrl = '#', $method = 'POST', $options = []) {
    $buttonText = $options['buttonText'] ?? $title;
    $buttonClass = $options['buttonClass'] ?? "btn-outline-{$color}";
    $buttonSize = $options['buttonSize'] ?? 'btn-sm';
    $modalId = $id . 'Modal';
    ob_start();
?>
<button class="btn <?=$buttonClass?> <?=$buttonSize?>" onclick="confirmAction_<?=$id?>('<?=htmlspecialchars($message)?>','<?=$actionUrl?>','<?=$method?>')"><?=$buttonText?></button>
<script>
function confirmAction_<?=$id?>(message,url,method){Swal.fire({title:'<?=$title?>',text:message,icon:'warning',showCancelButton:true,confirmButtonText:'<?=$options['confirmText']??'تأیید'?>',cancelButtonText:'<?=$options['cancelText']??'انصراف'?>',confirmButtonColor:'#<?=$color==='danger'?'e74a3b':($color==='warning'?'f6c23e':($color==='success'?'1cc88a':'4e73df'))?>'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action=url;f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>">'+(method!=='POST'?'<input type="hidden" name="_method" value="'+method+'">':'');document.body.appendChild(f);f.submit()}})}
</script>
<?php return ob_get_clean(); }
?>