<?php

/**
 * DotEnv class for loading environment variables from a .env file.
 */
class DotEnv {
    protected string $path;
    protected array $envVariables = [];

    /**
     * Constructor.
     *
     * @param string $filePath Full path to the .env file.
     * @throws InvalidArgumentException|RuntimeException
     */
    public function __construct(string $filePath) {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("The .env file at $filePath does not exist.");
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException("The .env file at $filePath is not readable.");
        }

        $this->path = $filePath;
    }

    /**
     * Load and parse the .env file.
     *
     * @return void
     */
    public function load(): void {
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Handle export keyword
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                $value = $this->sanitizeValue($value);
                $value = $this->convertValueType($value);

                $this->setEnvironmentVariable($key, $value);
                $this->envVariables[$key] = $value;
            } else {
                error_log("Malformed .env line: $line");
            }
        }
    }

    /**
     * Get the value of a specific env variable.
     */
    public function get(string $key, mixed $default = null): mixed {
        return $this->envVariables[$key] ?? $default;
    }

    /**
     * Manually set a variable.
     */
    public function set(string $key, mixed $value): void {
        $value = $this->convertValueType($value);
        $this->setEnvironmentVariable($key, $value);
        $this->envVariables[$key] = $value;
    }

    /**
     * Get all loaded environment variables.
     */
    public function all(): array {
        return $this->envVariables;
    }

    /**
     * Sanitize value (remove quotes, convert escapes).
     */
    protected function sanitizeValue(string $value): string {
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        return str_replace(['\n', '\t', '\r'], ["\n", "\t", "\r"], $value);
    }

    /**
     * Convert string value to appropriate type.
     */
    protected function convertValueType(string $value): mixed {
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }

    /**
     * Set variable in environment.
     */
    protected function setEnvironmentVariable(string $key, mixed $value): void {
        $stringVal = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        putenv("$key=$stringVal");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
