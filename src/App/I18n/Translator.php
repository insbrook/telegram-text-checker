<?php

namespace TelegramApp\App\I18n;

class Translator
{
    public const DEFAULT_LANG = 'en';

    protected string $lang = self::DEFAULT_LANG;

    protected array $dictionary = [];

    public function setLang(string $lang = self::DEFAULT_LANG)
    {
        $lang = substr($lang, 0, 2);
        $path = __DIR__ . '/translations/' . $lang . '.php';
        if (file_exists($path)) {
            if (!str_contains(realpath($path), __DIR__)) {
                // What if $lang = '../../../../../backdoor.php' ??
                return;
            }
            if (!isset($this->dictionary[$lang])) {
                $this->dictionary[$lang] = require $path;
            }
            $this->lang = $lang;
        } else {
            $this->setLang();
        }
    }

    /**
     * @param string $token
     * @return mixed|string
     */
    public function text(string $token)
    {
        if (
            !$this->lang ||
            empty($this->dictionary[$this->lang])
        ) {
            $this->setLang($this->lang);
        }

        return $this->dictionary[$this->lang][$token] ?? $token;
    }
}
