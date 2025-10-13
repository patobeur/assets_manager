<?php
class Language {
    private static $instance = null;
    private $translations = [];
    private $lang;

    private function __construct($lang = 'fr') {
        $this->lang = $lang;
        $this->loadTranslations();
    }

    public static function getInstance($lang = 'fr') {
        if (self::$instance === null) {
            self::$instance = new Language($lang);
        }
        return self::$instance;
    }

    private function loadTranslations() {
        $langFile = __DIR__ . '/../languages/' . $this->lang . '.json';
        if (file_exists($langFile)) {
            $this->translations = json_decode(file_get_contents($langFile), true);
        }
    }

    public function get($key, $default = '') {
        return $this->translations[$key] ?? $default;
    }

    public function getLang() {
        return $this->lang;
    }

    public function setLang($lang) {
        if ($this->lang !== $lang) {
            $this->lang = $lang;
            $this->loadTranslations();
        }
    }
}

function t($key, $default = '') {
    return Language::getInstance()->get($key, $default);
}