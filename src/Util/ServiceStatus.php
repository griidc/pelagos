<?php

namespace App\Util;

use Throwable;

class ServiceStatus
{
    public const STATUS_OK = 'ok';
    public const STATUS_ERROR = 'error';

    private string $status = self::STATUS_OK;
    private array $data = [];
    private ?Throwable $error = null;

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setData(array $data): void
    {
        $this->data[] = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setThrowable(?Throwable $error): self
    {
        $this->status = self::STATUS_ERROR;
        $this->error = $error;
        return $this;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->error;
    }

    public function getResults(): array
    {
        $results = [];

        $results['status'] = $this->status;

        if ($this->data) {
            $results['data'] = $this->data;
        }

        if ($this->error) {
            $results['error'] = $this->error->getMessage();
        }

        return $results;
    }

    public function __toString()
    {
        return (string) json_encode($this->getResults());
    }
}
