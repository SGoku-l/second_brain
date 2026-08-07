<?php

namespace App\Services;

use App\Models\ErrorLog;
use Throwable;

class ErrorLogger
{
    /**
     * Persist a safe, queryable application error without replacing Laravel's log output.
     *
     * @param  array<string, mixed>  $context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $exception = $context['exception'] ?? null;

        if ($exception instanceof Throwable) {
            $context['stack_trace'] = $exception->getTraceAsString();
            $context['exception_message'] = $exception->getMessage();
        }

        unset($context['exception']);

        ErrorLog::create([
            'user_id' => $context['user_id'] ?? auth()->id(),
            'source_id' => $context['source_id'] ?? null,
            'level' => strtolower($level),
            'message' => $this->redact($message),
            'context' => $this->redact($context),
            'exception_class' => $exception instanceof Throwable ? $exception::class : null,
        ]);
    }

    public function sanitize(mixed $value): mixed
    {
        return $this->redact($value);
    }

    private function redact(mixed $value, ?string $key = null): mixed
    {
        if ($key && preg_match('/(api[_-]?key|token|authorization|password|secret)/i', $key)) {
            return '[redacted]';
        }

        if (is_array($value)) {
            return collect($value)->map(fn (mixed $item, string|int $itemKey) => $this->redact($item, (string) $itemKey))->all();
        }

        if (is_string($value)) {
            return preg_replace('/([?&](?:key|api[_-]?key|token|access_token)=)[^&\s]+/i', '$1[redacted]', $value);
        }

        return $value;
    }
}
