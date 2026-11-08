<?php
namespace App\Helpers;

/**
 * Chart Helper Class
 * توابع کمکی برای تولید نمودارها با Chart.js
 */
class ChartHelper
{
    /**
     * Color palettes
     */
    private static $palettes = [
        'default' => ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9'],
        'pastel' => ['#a8e6cf','#dcedc1','#ffd3b6','#ffaaa5','#ff8b94','#c7ceea','#b5e7a0','#f7dc6f'],
        'vivid' => ['#e74c3c','#3498db','#2ecc71','#f39c12','#9b59b6','#1abc9c','#e67e22','#34495e'],
        'dark' => ['#1a1a2e','#16213e','#0f3460','#533483','#e94560','#c84b31','#2d4059','#ea5455'],
    ];

    /**
     * Generate chart configuration for bar chart
     * 
     * @param array $labels
     * @param array $datasets Array of ['label'=>'...','data'=>[...],'color'=>'...']
     * @param array $options Additional options
     * @return array
     */
    public static function bar($labels, $datasets, $options = [])
    {
        $datasets = self::prepareDatasets($datasets);

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => array_merge([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['position' => 'top'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true],
                ],
            ], $options),
        ];
    }

    /**
     * Generate chart configuration for line chart
     * 
     * @param array $labels
     * @param array $datasets
     * @param array $options
     * @return array
     */
    public static function line($labels, $datasets, $options = [])
    {
        $datasets = self::prepareDatasets($datasets, true);

        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => array_merge([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['position' => 'top'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true],
                ],
            ], $options),
        ];
    }

    /**
     * Generate chart configuration for pie chart
     * 
     * @param array $labels
     * @param array $data Single data array
     * @param array $options
     * @return array
     */
    public static function pie($labels, $data, $options = [])
    {
        $palette = $options['palette'] ?? 'default';
        $colors = self::$palettes[$palette] ?? self::$palettes['default'];

        return [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ]],
            ],
            'options' => array_merge([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => ['padding' => 20, 'usePointStyle' => true],
                    ],
                ],
            ], $options),
        ];
    }

    /**
     * Generate chart configuration for doughnut chart
     * 
     * @param array $labels
     * @param array $data
     * @param array $options
     * @return array
     */
    public static function doughnut($labels, $data, $options = [])
    {
        return self::pie($labels, $data, array_merge(['type' => 'doughnut'], $options));
    }

    /**
     * Generate chart configuration for polar area chart
     * 
     * @param array $labels
     * @param array $data
     * @param array $options
     * @return array
     */
    public static function polar($labels, $data, $options = [])
    {
        $palette = $options['palette'] ?? 'default';
        $colors = self::$palettes[$palette] ?? self::$palettes['default'];

        return [
            'type' => 'polarArea',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ]],
            ],
            'options' => array_merge([
                'responsive' => true,
                'maintainAspectRatio' => false,
            ], $options),
        ];
    }

    /**
     * Generate chart configuration for radar chart
     * 
     * @param array $labels
     * @param array $datasets
     * @param array $options
     * @return array
     */
    public static function radar($labels, $datasets, $options = [])
    {
        $datasets = self::prepareDatasets($datasets);

        return [
            'type' => 'radar',
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => array_merge([
                'responsive' => true,
                'maintainAspectRatio' => false,
            ], $options),
        ];
    }

    /**
     * Generate chart configuration for horizontal bar chart
     * 
     * @param array $labels
     * @param array $datasets
     * @param array $options
     * @return array
     */
    public static function horizontalBar($labels, $datasets, $options = [])
    {
        $config = self::bar($labels, $datasets, $options);
        $config['options']['indexAxis'] = 'y';
        return $config;
    }

    /**
     * Generate chart JavaScript code
     * 
     * @param string $canvasId Canvas element ID
     * @param array $config Chart configuration
     * @return string JavaScript code
     */
    public static function renderScript($canvasId, $config)
    {
        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return "new Chart(document.getElementById('{$canvasId}').getContext('2d'), {$json});";
    }

    /**
     * Generate full HTML for chart
     * 
     * @param string $canvasId
     * @param array $config
     * @param string $title
     * @param int $height Canvas height
     * @return string HTML
     */
    public static function render($canvasId, $config, $title = '', $height = 300)
    {
        $html = '';
        if ($title) {
            $html .= '<div class="card shadow mb-4">';
            $html .= '<div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">' . htmlspecialchars($title) . '</h6></div>';
            $html .= '<div class="card-body">';
        }

        $html .= '<canvas id="' . $canvasId . '" height="' . $height . '"></canvas>';
        $html .= '<script>' . self::renderScript($canvasId, $config) . '</script>';

        if ($title) {
            $html .= '</div></div>';
        }

        return $html;
    }

    /**
     * Get color from palette
     * 
     * @param int $index
     * @param string $palette
     * @return string
     */
    public static function getColor($index, $palette = 'default')
    {
        $colors = self::$palettes[$palette] ?? self::$palettes['default'];
        return $colors[$index % count($colors)];
    }

    /**
     * Generate gradient of colors
     * 
     * @param int $count Number of colors needed
     * @param string $palette Palette name
     * @return array
     */
    public static function getColors($count, $palette = 'default')
    {
        $colors = self::$palettes[$palette] ?? self::$palettes['default'];
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }

        return $result;
    }

    /**
     * Prepare datasets for chart
     * 
     * @param array $datasets
     * @param bool $isLine Whether this is a line chart
     * @return array
     */
    private static function prepareDatasets($datasets, $isLine = false)
    {
        $palette = self::$palettes['default'];
        $result = [];

        foreach ($datasets as $i => $dataset) {
            $color = $dataset['color'] ?? $palette[$i % count($palette)];

            $prepared = [
                'label' => $dataset['label'] ?? '',
                'data' => $dataset['data'] ?? [],
                'backgroundColor' => $dataset['backgroundColor'] ?? $color,
                'borderColor' => $dataset['borderColor'] ?? $color,
                'borderWidth' => $dataset['borderWidth'] ?? 1,
            ];

            if ($isLine) {
                $prepared['tension'] = $dataset['tension'] ?? 0.3;
                $prepared['fill'] = $dataset['fill'] ?? false;
                $prepared['pointRadius'] = $dataset['pointRadius'] ?? 4;
            }

            $result[] = $prepared;
        }

        return $result;
    }
}