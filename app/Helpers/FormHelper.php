<?php
namespace App\Helpers;

/**
 * Form Helper Class
 * توابع کمکی برای تولید فرم‌های HTML
 */
class FormHelper
{
    /**
     * Open form tag
     */
    public static function open($action = '', $method = 'POST', $attributes = [])
    {
        $attributes['action'] = $action;
        $attributes['method'] = $method === 'GET' ? 'GET' : 'POST';
        
        $html = '<form' . HtmlHelper::attributes($attributes) . '>';
        
        // Add CSRF token for non-GET methods
        if (!in_array(strtoupper($method), ['GET', 'HEAD'])) {
            $html .= '<input type="hidden" name="csrf_token" value="' . (session()->get('csrf_token') ?? '') . '">';
            
            // Add method spoofing for PUT, PATCH, DELETE
            if (!in_array(strtoupper($method), ['POST'])) {
                $html .= '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
            }
        }
        
        return $html;
    }
    
    /**
     * Close form tag
     */
    public static function close()
    {
        return '</form>';
    }
    
    /**
     * Create label
     */
    public static function label($for, $text, $attributes = [])
    {
        $attributes['for'] = $for;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-label';
        
        return HtmlHelper::tag('label', $text, $attributes);
    }
    
    /**
     * Create text input
     */
    public static function text($name, $value = '', $attributes = [])
    {
        $attributes['type'] = 'text';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create password input
     */
    public static function password($name, $attributes = [])
    {
        $attributes['type'] = 'password';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create email input
     */
    public static function email($name, $value = '', $attributes = [])
    {
        $attributes['type'] = 'email';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create number input
     */
    public static function number($name, $value = '', $attributes = [])
    {
        $attributes['type'] = 'number';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create date input
     */
    public static function date($name, $value = '', $attributes = [])
    {
        $attributes['type'] = 'date';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create time input
     */
    public static function time($name, $value = '', $attributes = [])
    {
        $attributes['type'] = 'time';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create file input
     */
    public static function file($name, $attributes = [])
    {
        $attributes['type'] = 'file';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create hidden input
     */
    public static function hidden($name, $value = '', $attributes = [])
    {
        $attributes['type'] = 'hidden';
        $attributes['name'] = $name;
        $attributes['value'] = $value;
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create textarea
     */
    public static function textarea($name, $value = '', $attributes = [])
    {
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control';
        $attributes['rows'] = $attributes['rows'] ?? 3;
        
        return HtmlHelper::tag('textarea', htmlspecialchars($value), $attributes);
    }
    
    /**
     * Create select dropdown
     */
    public static function select($name, $options = [], $selected = null, $attributes = [])
    {
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-select';
        
        $html = '';
        
        foreach ($options as $value => $text) {
            if (is_array($text)) {
                // Optgroup
                $html .= '<optgroup label="' . $value . '">';
                foreach ($text as $subValue => $subText) {
                    $selectedAttr = ($subValue == $selected) ? ' selected' : '';
                    $html .= '<option value="' . $subValue . '"' . $selectedAttr . '>' . $subText . '</option>';
                }
                $html .= '</optgroup>';
            } else {
                $selectedAttr = ($value == $selected) ? ' selected' : '';
                $html .= '<option value="' . $value . '"' . $selectedAttr . '>' . $text . '</option>';
            }
        }
        
        return HtmlHelper::tag('select', $html, $attributes);
    }
    
    /**
     * Create checkbox
     */
    public static function checkbox($name, $value = '1', $checked = false, $attributes = [])
    {
        $attributes['type'] = 'checkbox';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-check-input';
        
        if ($checked) {
            $attributes['checked'] = true;
        }
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create radio button
     */
    public static function radio($name, $value, $checked = false, $attributes = [])
    {
        $attributes['type'] = 'radio';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? ($name . '_' . $value);
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-check-input';
        
        if ($checked) {
            $attributes['checked'] = true;
        }
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create button
     */
    public static function button($text, $type = 'submit', $attributes = [])
    {
        $attributes['type'] = $type;
        $attributes['class'] = ($attributes['class'] ?? '') . ' btn btn-' . ($attributes['color'] ?? 'primary');
        
        return HtmlHelper::tag('button', $text, $attributes);
    }
    
    /**
     * Create form group with label and input
     */
    public static function group($label, $input, $error = null, $help = null)
    {
        $html = '<div class="mb-3">';
        $html .= $label;
        $html .= $input;
        
        if ($help) {
            $html .= '<div class="form-text">' . $help . '</div>';
        }
        
        if ($error) {
            $html .= '<div class="invalid-feedback d-block">' . $error . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Create switch/toggle
     */
    public static function switch($name, $checked = false, $attributes = [])
    {
        $attributes['type'] = 'checkbox';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-check-input';
        $attributes['role'] = 'switch';
        
        if ($checked) {
            $attributes['checked'] = true;
        }
        
        $html = '<div class="form-check form-switch">';
        $html .= HtmlHelper::tag('input', '', $attributes);
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Create color picker input
     */
    public static function color($name, $value = '#000000', $attributes = [])
    {
        $attributes['type'] = 'color';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-control form-control-color';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
    
    /**
     * Create range slider input
     */
    public static function range($name, $value = '', $min = 0, $max = 100, $attributes = [])
    {
        $attributes['type'] = 'range';
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        $attributes['value'] = $value;
        $attributes['min'] = $min;
        $attributes['max'] = $max;
        $attributes['class'] = ($attributes['class'] ?? '') . ' form-range';
        
        return HtmlHelper::tag('input', '', $attributes);
    }
}