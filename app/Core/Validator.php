<?php
namespace App\Core;

class Validator
{
    private $errors = [];
    private $validated = [];
    private $customMessages = [];
    
    public function validate($data, $rules, $messages = [])
    {
        $this->errors = [];
        $this->validated = [];
        $this->customMessages = $messages;
        
        foreach ($rules as $field => $ruleSet) {
            $rules = $this->parseRules($ruleSet);
            $value = $data[$field] ?? null;
            
            // Check if field is required and missing
            $isRequired = in_array('required', $rules);
            $isNullable = in_array('nullable', $rules);
            
            if ($isNullable && ($value === null || $value === '')) {
                $this->validated[$field] = null;
                continue;
            }
            
            if ($isRequired && ($value === null || $value === '')) {
                $this->addError($field, 'required', $this->getMessage($field, 'required', 'فیلد :attribute الزامی است'));
                continue;
            }
            
            if (!$isRequired && ($value === null || $value === '')) {
                $this->validated[$field] = $value;
                continue;
            }
            
            // Apply each validation rule
            foreach ($rules as $rule) {
                if (in_array($rule, ['required', 'nullable'])) {
                    continue;
                }
                
                $params = [];
                if (strpos($rule, ':') !== false) {
                    list($rule, $param) = explode(':', $rule, 2);
                    $params = explode(',', $param);
                }
                
                $method = 'validate' . str_replace('_', '', ucwords($rule, '_'));
                
                if (method_exists($this, $method)) {
                    $result = $this->$method($field, $value, $params, $data);
                    
                    if ($result !== true) {
                        $message = $this->getMessage($field, $rule, $result);
                        $this->addError($field, $rule, $message);
                        break; // Stop on first error for this field
                    }
                }
            }
            
            // Add to validated if no errors
            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }
        
        return empty($this->errors);
    }
    
    private function parseRules($ruleSet)
    {
        if (is_string($ruleSet)) {
            return explode('|', $ruleSet);
        }
        return $ruleSet;
    }
    
    private function getMessage($field, $rule, $default)
    {
        $customKey = "{$field}.{$rule}";
        if (isset($this->customMessages[$customKey])) {
            return $this->customMessages[$customKey];
        }
        
        $fieldName = $this->getFieldName($field);
        return str_replace(':attribute', $fieldName, $default);
    }
    
    private function getFieldName($field)
    {
        $names = [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'phone' => 'شماره تماس',
            'address' => 'آدرس',
            'name' => 'نام',
            'title' => 'عنوان',
            'description' => 'توضیحات',
            'date' => 'تاریخ',
            'amount' => 'مبلغ',
        ];
        
        return $names[$field] ?? $field;
    }
    
    public function addError($field, $rule, $message)
    {
        $this->errors[$field][] = $message;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getFirstError()
    {
        foreach ($this->errors as $errors) {
            return $errors[0] ?? '';
        }
        return '';
    }
    
    public function getValidated()
    {
        return $this->validated;
    }
    
    public function fails()
    {
        return !empty($this->errors);
    }
    
    public function passes()
    {
        return empty($this->errors);
    }
    
    // Validation Methods
    private function validateRequired($field, $value)
    {
        return ($value !== null && $value !== '') ? true : 'فیلد :attribute الزامی است';
    }
    
    private function validateEmail($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : ':attribute باید یک ایمیل معتبر باشد';
    }
    
    private function validateMin($field, $value, $params)
    {
        $min = (int)$params[0];
        if (is_string($value)) {
            return strlen($value) >= $min ? true : ":attribute باید حداقل {$min} کاراکتر باشد";
        }
        return $value >= $min ? true : ":attribute باید حداقل {$min} باشد";
    }
    
    private function validateMax($field, $value, $params)
    {
        $max = (int)$params[0];
        if (is_string($value)) {
            return strlen($value) <= $max ? true : ":attribute نباید بیشتر از {$max} کاراکتر باشد";
        }
        return $value <= $max ? true : ":attribute نباید بیشتر از {$max} باشد";
    }
    
    private function validateBetween($field, $value, $params)
    {
        $min = (int)$params[0];
        $max = (int)$params[1];
        
        if (is_string($value)) {
            $length = strlen($value);
        } else {
            $length = $value;
        }
        
        return ($length >= $min && $length <= $max) 
            ? true 
            : ":attribute باید بین {$min} و {$max} باشد";
    }
    
    private function validateNumeric($field, $value)
    {
        return is_numeric($value) ? true : ':attribute باید یک عدد باشد';
    }
    
    private function validateInteger($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? true : ':attribute باید یک عدد صحیح باشد';
    }
    
    private function validateString($field, $value)
    {
        return is_string($value) ? true : ':attribute باید یک رشته باشد';
    }
    
    private function validateArray($field, $value)
    {
        return is_array($value) ? true : ':attribute باید یک آرایه باشد';
    }
    
    private function validateUnique($field, $value, $params)
    {
        $table = $params[0];
        $column = $params[1] ?? $field;
        $ignoreId = $params[2] ?? null;
        $ignoreColumn = $params[3] ?? 'id';
        
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` = ?";
        $queryParams = [$value];
        
        if ($ignoreId) {
            $sql .= " AND `{$ignoreColumn}` != ?";
            $queryParams[] = $ignoreId;
        }
        
        $result = $db->selectOne($sql, $queryParams);
        
        return ($result['count'] ?? 0) === 0 ? true : ':attribute قبلاً ثبت شده است';
    }
    
    private function validateExists($field, $value, $params)
    {
        $table = $params[0];
        $column = $params[1] ?? $field;
        
        $db = Database::getInstance();
        $result = $db->selectOne(
            "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` = ?",
            [$value]
        );
        
        return ($result['count'] ?? 0) > 0 ? true : ':attribute انتخاب شده معتبر نیست';
    }
    
    private function validateIn($field, $value, $params)
    {
        return in_array($value, $params) ? true : ':attribute انتخاب شده معتبر نیست';
    }
    
    private function validateNotIn($field, $value, $params)
    {
        return !in_array($value, $params) ? true : ':attribute انتخاب شده معتبر نیست';
    }
    
    private function validateDate($field, $value)
    {
        return strtotime($value) !== false ? true : ':attribute یک تاریخ معتبر نیست';
    }
    
    private function validateAfter($field, $value, $params)
    {
        $compareDate = $params[0] ?? 'today';
        
        if ($compareDate === 'today') {
            $compareDate = date('Y-m-d');
        }
        
        return strtotime($value) > strtotime($compareDate) 
            ? true 
            : ":attribute باید بعد از {$compareDate} باشد";
    }
    
    private function validateBefore($field, $value, $params)
    {
        $compareDate = $params[0] ?? 'today';
        
        if ($compareDate === 'today') {
            $compareDate = date('Y-m-d');
        }
        
        return strtotime($value) < strtotime($compareDate) 
            ? true 
            : ":attribute باید قبل از {$compareDate} باشد";
    }
    
    private function validateConfirmed($field, $value, $params, $data)
    {
        $confirmationField = $field . '_confirmation';
        return (isset($data[$confirmationField]) && $value === $data[$confirmationField]) 
            ? true 
            : ':attribute با تاییدیه مطابقت ندارد';
    }
    
    private function validateRegex($field, $value, $params)
    {
        return preg_match($params[0], $value) ? true : ':attribute فرمت نامعتبری دارد';
    }
    
    private function validateUrl($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) ? true : ':attribute باید یک URL معتبر باشد';
    }
    
    private function validateIp($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) ? true : ':attribute باید یک IP معتبر باشد';
    }
    
    private function validateJson($field, $value)
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE ? true : ':attribute باید یک JSON معتبر باشد';
    }
    
    private function validateFile($field, $value)
    {
        return (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) 
            ? true 
            : 'فایل :attribute الزامی است';
    }
    
    private function validateImage($field, $value)
    {
        if (!isset($_FILES[$field])) {
            return 'فایل :attribute الزامی است';
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES[$field]['type'] ?? '';
        
        return in_array($fileType, $allowedTypes) 
            ? true 
            : ':attribute باید یک تصویر معتبر باشد';
    }
    
    private function validateMimes($field, $value, $params)
    {
        if (!isset($_FILES[$field])) {
            return 'فایل :attribute الزامی است';
        }
        
        $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        return in_array($extension, $params) 
            ? true 
            : ':attribute باید یکی از فرمت‌های ' . implode(', ', $params) . ' باشد';
    }
    
    private function validateMaxSize($field, $value, $params)
    {
        if (!isset($_FILES[$field])) {
            return true;
        }
        
        $maxSize = (int)$params[0] * 1024; // Convert KB to bytes
        return $_FILES[$field]['size'] <= $maxSize 
            ? true 
            : ":attribute نباید بیشتر از {$params[0]} کیلوبایت باشد";
    }
    
    private function validateDigits($field, $value, $params)
    {
        $length = (int)$params[0];
        return (strlen($value) === $length && ctype_digit($value)) 
            ? true 
            : ":attribute باید {$length} رقم باشد";
    }
}