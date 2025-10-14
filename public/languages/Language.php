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
        $langFile = __DIR__ . '/' . $this->lang . '.json';
        if (file_exists($langFile)) {
            $this->translations = json_decode(file_get_contents($langFile), true);
        }
    }

    public function get($key, $args = [], $default = '') {
        $value = $this->translations[$key] ?? $default;

        if (!empty($args) && is_array($args)) {
            foreach ($args as $arg_key => $arg_val) {
                $value = str_replace('{' . $arg_key . '}', $arg_val, $value);
            }
        }

        return $value;
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

function t($key, $args = [], $default = '') {
    // If the second argument is a string, it's the default value (for backward compatibility)
    if (is_string($args)) {
        $default = $args;
        $args = [];
    }
    return Language::getInstance()->get($key, $args, $default);
}