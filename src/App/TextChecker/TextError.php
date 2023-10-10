<?php

namespace TelegramApp\App\TextChecker;

use TelegramApp\Framework\Exception\BotException;
use TelegramApp\Framework\Exception\ClientSafeException;

class TextError implements \JsonSerializable
{
    public const TYPE_SPELLING = 'spelling';
    public const TYPE_GRAMMAR = 'grammar';
    public const TYPE_PUNCTUATION = 'punctuation';
    public const TYPE_TYPOGRAPHY = 'typography';

    public const PUNCTUATION_ARRAY = ['¸','·','¶','©','®','→','«','»','.',',','!','?','"','\'','’','”','"'];

    protected int $offset;

    protected int $errataLength;

    protected string $errataText;

    protected array $changeSuggestions = [];

    protected string $type;

    protected array $description;

    protected string $id;

    public function __construct(iterable $initial)
    {
        if (isset($initial['offset'])) {
            if (!is_numeric($initial['offset'])) {
                throw new BotException("Error offset must be numeric");
            }
            $this->setOffset($initial['offset']);
        }


        if (isset($initial['length'])) {
            if (!is_numeric($initial['length'])) {
                throw new BotException("Errata text length must be numeric");
            }
            $this->setErrataLength($initial['length']);
        }


        if (isset($initial['description'])) {
            if (!is_array($initial['description'])) {
                throw new BotException("Error description must be an array of descriptions for different languages");
            }
            $this->setDescription($initial['description']);
        }


        if (isset($initial['bad'])) {
            if (
                !is_string($initial['bad']) &&
                !($initial['bad'] instanceof \Stringable)
            ) {
                throw new BotException("Errata text must be string or stringable");
            }
            $this->setErrataText((string)$initial['bad']);
        }

        if (isset($initial['better'])) {
            if (!is_array($initial['better'])) {
                throw new BotException("Fix suggestions must be an array");
            }
            $this->setChangeSuggestions($initial['better']);
        }


        if (isset($initial['type'])) {
            if (!is_string($initial['type'])) {
                throw new BotException("Error type must be string");
            }
            $this->setType($initial['type']);
        }
        $this->id = 'e' . mt_rand(0, mt_getrandmax());
    }

    /**
     * Method to automate response json encoding
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'offset' => $this->offset,
            'length' => $this->errataLength,
            'description' => $this->description,
            'bad' => $this->errataText,
            'better' => $this->changeSuggestions,
            'type' => $this->getType(),
        ];
    }

    /**
     * @param string $text
     * @return string
     */
    public function fix(string $text): string
    {
        if (!$this->changeSuggestions) {
            return $text;
        }

        if ($this->changeSuggestions[0]) {
            return mb_substr($text, 0, $this->offset) .
                $this->changeSuggestions[0] .
                mb_substr($text, $this->offset + $this->errataLength);
        }

        $before = mb_substr($text, 0, $this->offset);
        $after = mb_substr($text, $this->offset + $this->errataLength);

        if (!$before) {
            return ltrim($after);
        }
        if (!$after) {
            return rtrim($before);
        }

        // Strip merged spaces before and after removed piece
        $charBefore = mb_substr($before, -1);
        $charAfter = mb_substr($after, 0, 1);
        if (
            ' ' === $charBefore &&
            (
                $charAfter === ' ' ||
                $charAfter === "\n" ||
                in_array($charAfter, static::PUNCTUATION_ARRAY)
            )
        ) {
            return mb_substr($before, 0, -1) . $after;
        }

        return $before . $after;
    }

    /**
     * Error type getter
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type ?: static::TYPE_SPELLING;
    }

    /**
     * Error type setter
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * The number of chars before the first errata text char
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    /**
     * Length of errata text. In UTF-8 chars, not bytes.
     *
     * @return int
     */
    public function getErrataLength(): int
    {
        return $this->errataLength;
    }

    /**
     * @param int $errataLength
     */
    public function setErrataLength(int $errataLength)
    {
        $this->errataLength = $errataLength;
    }

    /**
     * Part of initial text to be replaced
     *
     * @return string
     */
    public function getErrataText(): string
    {
        return $this->errataText;
    }

    /**
     * @param string $errataText
     */
    public function setErrataText(string $errataText)
    {
        $this->errataText = $errataText;
    }

    /**
     * An array of possible error text replacements
     *
     * @return string[]
     */
    public function getChangeSuggestions(): array
    {
        return $this->changeSuggestions;
    }

    /**
     * @param string[] $changeSuggestions
     */
    public function setChangeSuggestions(array $changeSuggestions)
    {
        $this->changeSuggestions = $changeSuggestions;
    }

    /**
     * Unique id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * An array of text explanations
     *
     * @return array
     */
    public function getDescription(): ?array
    {
        return $this->description;
    }

    /**
     * @param array $description
     */
    public function setDescription(array $description)
    {
        $this->description = $description;
    }
}
