<?php

namespace App\Entity;

interface EntityInterface
{
    public function getId(): ?int;

    public function checkDeletable(): void;

    public function isDeletable(): bool;

    public function getUnderscoredName(): string;
}