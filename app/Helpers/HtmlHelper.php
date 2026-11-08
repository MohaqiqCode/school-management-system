<?php
namespace App\Helpers;

/**
 * HTML Helper Class
 * توابع کمکی برای تولید HTML
 */
class HtmlHelper
{
    /**
     * Create HTML tag
     */
    public static function tag($tag, $content = '', $attributes = [])
    {
        $attrString = self::attributes($attributes);
        
        if (in_array($tag, ['br', 'hr', 'img', 'input', 'meta', 'link', 'area', 'base', 'col', 'embed', 'source', 'track', 'wbr'])) {
            return "<{$tag}{$attrString}>";
        }
        
        return "<{$tag}{$attrString}>{$content}</{$tag}>";
    }
    
    /**
     * Convert array to HTML attributes
     */
    public static function attributes($attributes)
    {
        if (empty($attributes)) {
            return '';
        }
        
        $html = '';
        
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " {$key}";
                }
            } elseif (is_array($value)) {
                if ($key === 'style') {
                    $styles = [];
                    foreach ($value as $prop => $val) {
                        $styles[] = "{$prop}: {$val}";
                    }
                    $html .= " style=\"" . implode('; ', $styles) . "\"";
                } elseif ($key === 'class') {
                    $html .= " class=\"" . implode(' ', $value) . "\"";
                } else {
                    $html .= " {$key}=\"" . implode(' ', $value) . "\"";
                }
            } else {
                $html .= " {$key}=\"" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "\"";
            }
        }
        
        return $html;
    }
    
    /**
     * Create link
     */
    public static function link($url, $text = null, $attributes = [])
    {
        if (is_null($text)) {
            $text = $url;
        }
        
        $attributes['href'] = $url;
        
        return self::tag('a', $text, $attributes);
    }
    
    /**
     * Create image tag
     */
    public static function image($src, $alt = '', $attributes = [])
    {
        $attributes['src'] = $src;
        $attributes['alt'] = $alt;
        
        return self::tag('img', '', $attributes);
    }
    
    /**
     * Create script tag
     */
    public static function script($src, $attributes = [])
    {
        $attributes['src'] = $src;
        
        return self::tag('script', '', $attributes);
    }
    
    /**
     * Create stylesheet link
     */
    public static function style($href, $attributes = [])
    {
        $attributes['rel'] = 'stylesheet';
        $attributes['href'] = $href;
        
        return self::tag('link', '', $attributes);
    }
    
    /**
     * Create meta tag
     */
    public static function meta($name, $content, $attributes = [])
    {
        $attributes['name'] = $name;
        $attributes['content'] = $content;
        
        return self::tag('meta', '', $attributes);
    }
    
    /**
     * Create ordered list
     */
    public static function ol($items, $attributes = [])
    {
        $html = '';
        
        foreach ($items as $item) {
            $html .= self::tag('li', $item);
        }
        
        return self::tag('ol', $html, $attributes);
    }
    
    /**
     * Create unordered list
     */
    public static function ul($items, $attributes = [])
    {
        $html = '';
        
        foreach ($items as $item) {
            if (is_array($item)) {
                $html .= self::tag('li', $item['text'] . self::ul($item['children'] ?? []));
            } else {
                $html .= self::tag('li', $item);
            }
        }
        
        return self::tag('ul', $html, $attributes);
    }
    
    /**
     * Create table
     */
    public static function table($headers, $rows, $attributes = [])
    {
        $html = '<thead><tr>';
        
        foreach ($headers as $header) {
            $html .= self::tag('th', $header);
        }
        
        $html .= '</tr></thead><tbody>';
        
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= self::tag('td', $cell);
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        
        return self::tag('table', $html, $attributes);
    }
    
    /**
     * Create badge
     */
    public static function badge($text, $type = 'primary', $attributes = [])
    {
        $attributes['class'] = 'badge badge-' . $type;
        return self::tag('span', $text, $attributes);
    }
    
    /**
     * Create alert
     */
    public static function alert($message, $type = 'info', $dismissible = false)
    {
        $attributes = ['class' => 'alert alert-' . $type, 'role' => 'alert'];
        
        $html = '';
        
        if ($dismissible) {
            $attributes['class'] .= ' alert-dismissible fade show';
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }
        
        $html .= $message;
        
        return self::tag('div', $html, $attributes);
    }
    
    /**
     * Create card
     */
    public static function card($header, $body, $footer = null, $attributes = [])
    {
        $attributes['class'] = 'card';
        
        $html = '';
        
        if ($header) {
            $html .= self::tag('div', $header, ['class' => 'card-header']);
        }
        
        $html .= self::tag('div', $body, ['class' => 'card-body']);
        
        if ($footer) {
            $html .= self::tag('div', $footer, ['class' => 'card-footer']);
        }
        
        return self::tag('div', $html, $attributes);
    }
    
    /**
     * Create progress bar
     */
    public static function progressBar($value, $max = 100, $type = 'primary', $attributes = [])
    {
        $percentage = round(($value / $max) * 100);
        
        $attributes['class'] = 'progress-bar bg-' . $type;
        $attributes['role'] = 'progressbar';
        $attributes['style'] = "width: {$percentage}%";
        $attributes['aria-valuenow'] = $value;
        $attributes['aria-valuemin'] = 0;
        $attributes['aria-valuemax'] = $max;
        
        $inner = self::tag('div', "{$percentage}%", $attributes);
        
        return self::tag('div', $inner, ['class' => 'progress']);
    }
    
    /**
     * Create star rating
     */
    public static function starRating($rating, $max = 5)
    {
        $html = '';
        
        for ($i = 1; $i <= $max; $i++) {
            if ($i <= $rating) {
                $html .= '<i class="fas fa-star text-warning"></i>';
            } elseif ($i - 0.5 <= $rating) {
                $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
            } else {
                $html .= '<i class="far fa-star text-warning"></i>';
            }
        }
        
        return self::tag('span', $html, ['class' => 'star-rating']);
    }
    
    /**
     * Create pagination HTML
     */
    public static function pagination($currentPage, $totalPages, $url, $maxVisible = 7)
    {
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<ul class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            $html .= '<li class="page-item">' . self::link($url . '?page=' . ($currentPage - 1), '&laquo;', ['class' => 'page-link']) . '</li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
        }
        
        // Page numbers
        $pages = [];
        
        if ($totalPages <= $maxVisible) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            $pages[] = 1;
            
            $start = max(2, $currentPage - 2);
            $end = min($totalPages - 1, $currentPage + 2);
            
            if ($start > 2) {
                $pages[] = '...';
            }
            
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
            
            if ($end < $totalPages - 1) {
                $pages[] = '...';
            }
            
            $pages[] = $totalPages;
        }
        
        foreach ($pages as $page) {
            if ($page === '...') {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            } elseif ($page == $currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
            } else {
                $html .= '<li class="page-item">' . self::link($url . '?page=' . $page, $page, ['class' => 'page-link']) . '</li>';
            }
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $html .= '<li class="page-item">' . self::link($url . '?page=' . ($currentPage + 1), '&raquo;', ['class' => 'page-link']) . '</li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Create breadcrumb
     */
    public static function breadcrumb($items)
    {
        $html = '<ol class="breadcrumb">';
        
        $last = array_pop($items);
        
        foreach ($items as $text => $url) {
            if (is_numeric($text)) {
                $text = $url;
                $html .= '<li class="breadcrumb-item">' . self::link($url, $text) . '</li>';
            } else {
                $html .= '<li class="breadcrumb-item">' . self::link($url, $text) . '</li>';
            }
        }
        
        $html .= '<li class="breadcrumb-item active" aria-current="page">' . $last . '</li>';
        $html .= '</ol>';
        
        return $html;
    }
    
    /**
     * Create icon
     */
    public static function icon($name, $class = '')
    {
        return '<i class="' . $name . ' ' . $class . '"></i>';
    }
    
    /**
     * Create dropdown menu
     */
    public static function dropdown($label, $items, $attributes = [])
    {
        $id = 'dropdown-' . uniqid();
        
        $html = '<div class="dropdown">';
        $html .= '<button class="btn btn-secondary dropdown-toggle" type="button" id="' . $id . '" data-bs-toggle="dropdown" aria-expanded="false">';
        $html .= $label;
        $html .= '</button>';
        $html .= '<ul class="dropdown-menu" aria-labelledby="' . $id . '">';
        
        foreach ($items as $item) {
            if ($item === 'divider') {
                $html .= '<li><hr class="dropdown-divider"></li>';
            } elseif (isset($item['header'])) {
                $html .= '<li><h6 class="dropdown-header">' . $item['header'] . '</h6></li>';
            } else {
                $text = $item['text'] ?? '';
                $url = $item['url'] ?? '#';
                $icon = $item['icon'] ?? '';
                $html .= '<li>' . self::link($url, self::icon($icon) . ' ' . $text, ['class' => 'dropdown-item']) . '</li>';
            }
        }
        
        $html .= '</ul></div>';
        
        return $html;
    }
    
    /**
     * Create modal HTML
     */
    public static function modal($id, $title, $body, $footer = '')
    {
        $html = '<div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true">';
        $html .= '<div class="modal-dialog">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header">';
        $html .= '<h5 class="modal-title" id="' . $id . 'Label">' . $title . '</h5>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        $html .= '</div>';
        $html .= '<div class="modal-body">' . $body . '</div>';
        
        if ($footer) {
            $html .= '<div class="modal-footer">' . $footer . '</div>';
        }
        
        $html .= '</div></div></div>';
        
        return $html;
    }
}