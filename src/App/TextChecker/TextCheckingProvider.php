<?php

namespace TelegramApp\App\TextChecker;

use LanguageDetection\Language;
use Psr\Container\ContainerInterface;
use TelegramApp\App\TextChecker\Exceptions\UnsupportedLanguageException;

class TextCheckingProvider
{
    public function __construct(
        protected Language $languageDetector,
        protected ContainerInterface $container,
        protected array $checkers,
        protected array $allowedLanguages,
    ) {
        //
    }

    protected function getTextLanguage(string $text)
    {
        // Language detector works with short codes.
        // You can implement your own detector to work with dialects
        // or train this LanguageDetector for this purposes.
        $codes = [];
        foreach ($this->allowedLanguages as $isoCode) {
            $codes[substr($isoCode, 0, 2)] = $isoCode;
        }

        $result = $this->languageDetector->detect($text);
        if (mb_strlen($text) <= 30) {
            // For small texts detect for a limited number of languages
            $result = $result->whitelist(...['en', 'ru', 'ar', 'ja', 'zh', 'fa']);
        } else {
            $result = $result->whitelist(...array_keys($codes));
        }
        /*
         * Are there any suggested languages from the initial allow-list?
         */
        $best = $result->close();

        foreach ($best as $lang => $_) {
            return $codes[$lang];
        }

        throw new UnsupportedLanguageException();
    }

    /**
     * @param string $text
     * @return TextError[]|void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getErrors(string $text)
    {
        if (!$text) {
            return [];
        }

        $language = $this->getTextLanguage($text);

        foreach ($this->checkers as $checkerClass) {
            $checker = $this->container->get($checkerClass);
            assert($checker instanceof TextCheckerInterface);
            if (!$checker->isAvailable()) {
                continue;
            }
            return $checker->getErrors($text, $language);
        }

        return [];
    }

    /**
     * @param string $text
     * @param int $foundErrors
     * @return string
     */
    public function getCorrected(string $text, &$foundErrors = 0)
    {
        $errors = $this->getErrors($text);
        // We'll fix the text from the end to the beginning
        usort($errors, function (TextError $a, TextError $b) {
            return $b->getOffset() <=> $a->getOffset();
        });
        foreach ($errors as $error) {
            $text = $error->fix($text);
        }
        $foundErrors = count($errors);
        return $text;
    }

    public function getTextWithErrors(string $text): array
    {
        $errors = $this->getErrors($text);
        if (!$errors) {
            return [
                'text' => $text,
                'entities' => null,
            ];
        }

        $textWithErrors = '';
        $entities = [];
        $caret = 0;
        foreach ($errors as $error) {
            $textWithErrors .= mb_substr($text, $caret, $error->getOffset() - $caret);
            $entities[] = [
                'type' => 'strikethrough',
                'offset' => mb_strlen($textWithErrors),
                'length' => $error->getErrataLength(),
            ];
            $textWithErrors .= $error->getErrataText();
            $caret = $error->getOffset() + $error->getErrataLength();

            $suggestion = $error->getChangeSuggestions()[0];

            if (!$suggestion) {
                continue;
            }

            $suggestion = "[{$suggestion}]";
            $entities[] = [
                'type' => 'bold',
                'offset' => mb_strlen($textWithErrors),
                'length' => mb_strlen($suggestion),
            ];
            $textWithErrors .= $suggestion;
        }

        if ($caret < mb_strlen($text)) {
            $textWithErrors .= mb_substr($text, $caret);
        }

        return [
            'text' => $textWithErrors,
            'entities' => $entities,
        ];
    }
}
