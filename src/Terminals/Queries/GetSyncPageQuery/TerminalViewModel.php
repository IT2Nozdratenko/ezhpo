<?php
declare(strict_types=1);

namespace Src\Terminals\Queries\GetSyncPageQuery;

final class TerminalViewModel
{
    /** @var int */
    private $id;

    /** @var string */
    private $text;

    public function __construct(int $id, string $text)
    {
        $this->id = $id;
        $this->text = $text;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
        ];
    }
}
