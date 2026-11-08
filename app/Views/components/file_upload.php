<?php
/**
 * File Upload Component
 * 
 * Usage:
 *   echo file_upload_area('document', 'آپلود مدرک', ['accept'=>'.pdf,.jpg','maxSize'=>5]);
 *   echo file_upload_dropzone('photos', 'آپلود تصاویر');
 */
function file_upload_area($name, $label = 'آپلود فایل', $options = []) {
    $id = $options['id'] ?? $name;
    $accept = $options['accept'] ?? '*';
    $maxSize = $options['maxSize'] ?? 5;
    $preview = $options['preview'] ?? '';
    $required = ($options['required'] ?? false) ? 'required' : '';
    ob_start();
?>
<div class="mb-3">
    <label class="form-label fw-bold"><?= $label ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
    <div class="upload-area border rounded-3 p-4 text-center bg-light" id="dropZone_<?=$id?>" onclick="document.getElementById('<?=$id?>').click()">
        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
        <p class="mb-1">فایل را اینجا رها کنید یا کلیک کنید</p>
        <small class="text-muted">فرمت‌های مجاز: <?=$accept?> | حداکثر <?=$maxSize?>MB</small>
        <input type="file" class="d-none" id="<?=$id?>" name="<?=$name?>" accept="<?=$accept?>" <?=$required?> onchange="previewFile(this,'preview_<?=$id?>')">
    </div>
    <div id="preview_<?=$id?>" class="mt-2 d-none"><div class="d-flex align-items-center justify-content-between p-2 bg-light rounded"><span id="fileName_<?=$id?>"></span><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile('<?=$id?>')"><i class="fas fa-times"></i></button></div></div>
    <?php if ($preview): ?><div class="mt-2"><img src="<?=$preview?>" class="rounded" style="max-height:80px;"></div><?php endif; ?>
</div>
<script>
(function(){const dz=document.getElementById('dropZone_<?=$id?>');['dragover','dragenter'].forEach(e=>dz.addEventListener(e,ev=>{ev.preventDefault();dz.classList.add('bg-primary','bg-opacity-10')}));['dragleave','drop'].forEach(e=>dz.addEventListener(e,ev=>{ev.preventDefault();dz.classList.remove('bg-primary','bg-opacity-10')}));dz.addEventListener('drop',e=>{document.getElementById('<?=$id?>').files=e.dataTransfer.files;previewFile({files:e.dataTransfer.files},'preview_<?=$id?>')})})();
function previewFile(input,previewId){if(input.files&&input.files[0]){const p=document.getElementById(previewId);p.classList.remove('d-none');document.getElementById('fileName_<?=$id?>').textContent=input.files[0].name}}
function removeFile(id){document.getElementById(id).value='';document.getElementById('preview_'+id).classList.add('d-none')}
</script>
<?php return ob_get_clean(); }

function file_upload_dropzone($name, $label = 'آپلود فایل‌ها', $options = []) {
    $id = $options['id'] ?? $name;
    $maxFiles = $options['maxFiles'] ?? 5;
    $maxSize = $options['maxSize'] ?? 10;
    ob_start();
?>
<div class="mb-3"><label class="form-label fw-bold"><?=$label?></label><div class="dropzone rounded border p-3 bg-light" id="<?=$id?>"><div class="dz-message text-center"><i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i><p>فایل‌ها را اینجا رها کنید</p><small>حداکثر <?=$maxFiles?> فایل - هر فایل <?=$maxSize?>MB</small></div></div></div>
<script>new Dropzone("#<?=$id?>",{url:"<?=config('app_url')?>/api/upload",paramName:"<?=$name?>",maxFiles:<?=$maxFiles?>,maxFilesize:<?=$maxSize?>,acceptedFiles:"image/*,.pdf,.doc,.docx",dictDefaultMessage:"",dictFallbackMessage:"مرورگر شما پشتیبانی نمی‌کند",dictFileTooBig:"فایل بزرگتر از حد مجاز است",dictInvalidFileType:"فرمت فایل مجاز نیست",dictMaxFilesExceeded:"حداکثر تعداد فایل",headers:{"X-CSRF-TOKEN":"<?=$_SESSION['csrf_token']??''?>"}})</script>
<?php return ob_get_clean(); }
?>