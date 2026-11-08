<?php
/**
 * Charts Component
 * 
 * Usage:
 *   echo chart_bar('myChart', ['فروردین','اردیبهشت'], [10,20], ['label'=>'فروش','color'=>'#4e73df']);
 *   echo chart_line('revenueChart', $labels, $data);
 *   echo chart_pie('distributionChart', $labels, $data);
 *   echo chart_doughnut('doughnutChart', $labels, $data);
 */

$chartColors = [
    'primary' => '#4e73df', 'success' => '#1cc88a', 'info' => '#36b9cc',
    'warning' => '#f6c23e', 'danger' => '#e74a3b', 'secondary' => '#858796',
    'dark' => '#5a5c69', 'light' => '#f8f9fc',
];

$paletteColors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];

/**
 * نمودار میله‌ای
 */
function chart_bar($id, $labels, $data, $options = []) {
    global $chartColors;
    $title = $options['title'] ?? '';
    $height = $options['height'] ?? 300;
    $horizontal = $options['horizontal'] ?? false;
    $color = $options['color'] ?? $chartColors['primary'];
    ob_start();
?>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?=$title?></h6></div><div class="card-body"><canvas id="<?=$id?>" height="<?=$height?>"></canvas></div></div>
<script>new Chart(document.getElementById('<?=$id?>').getContext('2d'),{type:'<?=$horizontal?'bar':'bar'?>',data:{labels:<?=json_encode($labels)?>,datasets:[{label:'<?=$options['label']??''?>',data:<?=json_encode(array_map('floatval',$data))?>,backgroundColor:'<?=$color?>',borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'<?=$horizontal?'y':'x'?>',plugins:{legend:{display:!!'<?=$options['label']??''?>'}},scales:{y:{beginAtZero:true}}}});</script>
<?php return ob_get_clean(); }

/**
 * نمودار خطی
 */
function chart_line($id, $labels, $datasets, $options = []) {
    global $chartColors;
    $title = $options['title'] ?? '';
    $height = $options['height'] ?? 300;
    $fill = $options['fill'] ?? false;
    ob_start();
?>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?=$title?></h6></div><div class="card-body"><canvas id="<?=$id?>" height="<?=$height?>"></canvas></div></div>
<script>new Chart(document.getElementById('<?=$id?>').getContext('2d'),{type:'line',data:{labels:<?=json_encode($labels)?>,datasets:<?=json_encode($datasets)?>},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true}}}});</script>
<?php return ob_get_clean(); }

/**
 * نمودار دایره‌ای
 */
function chart_pie($id, $labels, $data, $options = []) {
    global $paletteColors;
    $title = $options['title'] ?? '';
    $height = $options['height'] ?? 300;
    ob_start();
?>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?=$title?></h6></div><div class="card-body"><canvas id="<?=$id?>" height="<?=$height?>"></canvas></div></div>
<script>new Chart(document.getElementById('<?=$id?>').getContext('2d'),{type:'pie',data:{labels:<?=json_encode($labels)?>,datasets:[{data:<?=json_encode(array_map('floatval',$data))?>,backgroundColor:<?=json_encode(array_slice($paletteColors,0,count($data)))?>}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',rtl:true,labels:{padding:20,usePointStyle:true}}}}});</script>
<?php return ob_get_clean(); }

/**
 * نمودار دوناتی
 */
function chart_doughnut($id, $labels, $data, $options = []) {
    global $paletteColors;
    $title = $options['title'] ?? '';
    $height = $options['height'] ?? 300;
    ob_start();
?>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?=$title?></h6></div><div class="card-body"><canvas id="<?=$id?>" height="<?=$height?>"></canvas></div></div>
<script>new Chart(document.getElementById('<?=$id?>').getContext('2d'),{type:'doughnut',data:{labels:<?=json_encode($labels)?>,datasets:[{data:<?=json_encode(array_map('floatval',$data))?>,backgroundColor:<?=json_encode(array_slice($paletteColors,0,count($data)))?>,hoverBorderColor:'rgba(234,236,244,1)'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',rtl:true,labels:{padding:20,usePointStyle:true}}}}});</script>
<?php return ob_get_clean(); }
?>