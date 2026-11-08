<?php
namespace App\Middleware;

/**
 * Localization Middleware
 * مدیریت زبان و محلی‌سازی
 */
class LocalizationMiddleware extends Middleware
{
    /**
     * Handle localization
     * 
     * @return bool
     */
    public function handle()
    {
        // Get default locale from config
        $defaultLocale = $this->config['app_locale'] ?? 'fa';
        $availableLocales = $this->config['app_locales'] ?? ['fa' => 'فارسی'];
        
        // Detect locale from URL
        $uri = $this->request->getUri();
        $segments = explode('/', trim($uri, '/'));
        
        $locale = null;
        
        // Check if first segment is a locale
        if (!empty($segments[0]) && isset($availableLocales[$segments[0]])) {
            $locale = $segments[0];
        }
        
        // Check session
        if (!$locale) {
            $locale = $this->session->get('locale');
        }
        
        // Check cookie
        if (!$locale) {
            $locale = $_COOKIE['locale'] ?? null;
        }
        
        // Check browser language
        if (!$locale) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fa', 0, 2);
            $locale = isset($availableLocales[$browserLang]) ? $browserLang : null;
        }
        
        // Fallback to default
        if (!$locale) {
            $locale = $defaultLocale;
        }
        
        // Set locale
        $this->session->set('locale', $locale);
        setcookie('locale', $locale, time() + (86400 * 365), '/');
        
        // Set PHP locale
        $phpLocales = [
            'fa' => 'fa_IR.UTF-8',
            'en' => 'en_US.UTF-8',
        ];
        
        $phpLocale = $phpLocales[$locale] ?? 'fa_IR.UTF-8';
        setlocale(LC_ALL, $phpLocale);
        
        // Set direction
        $rtlLocales = ['fa', 'ar', 'he'];
        $direction = in_array($locale, $rtlLocales) ? 'rtl' : 'ltr';
        $this->session->set('direction', $direction);
        
        return true;
    }
}