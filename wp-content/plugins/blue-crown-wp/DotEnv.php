<?php
/*class DotEnv {
    protected string $path;
    protected array $envVariables = [];

    public function __construct(string $path) {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('The .env file at %s does not exist', $path));
        }

        if (!is_readable($path)) {
            throw new \RuntimeException(sprintf('The .env file at %s is not readable', $path));
        }

        $this->path = $path;
    }

    public function load(): void {
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (str_starts_with($line, '#') || empty($line)) {
                continue;
            }

            // Handle "export KEY=VALUE" syntax
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            // Parse key-value pair
            if (strpos($line, '=') !== false) {
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = $this->sanitizeValue(trim($value));

                // Convert value to appropriate type
                $value = $this->convertValueType($value);

                // Set environment variables
                $this->setEnvironmentVariable($name, $value);

                // Store in internal array for flexibility
                $this->envVariables[$name] = $value;
            } else {
                // Log or handle malformed lines
                error_log(sprintf('Malformed line in .env file: %s', $line));
            }
        }
    }

    protected function sanitizeValue(string $value): string {
        // Remove surrounding quotes if present
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        // Replace escaped characters (e.g., \n, \t)
        $value = str_replace(['\n', '\t', '\r'], ["\n", "\t", "\r"], $value);

        return $value;
    }

    protected function convertValueType(string $value): mixed {
        // Convert boolean-like strings to actual booleans
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }

        // Convert numeric strings to integers or floats
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        return $value;
    }

    protected function setEnvironmentVariable(string $name, mixed $value): void {
        // Convert value to string for environment variables
        $stringValue = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;

        // Set the environment variable
        putenv(sprintf('%s=%s', $name, $stringValue));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    public function get(string $name, mixed $default = null): mixed {
        return $this->envVariables[$name] ?? $default;
    }

    public function set(string $name, mixed $value): void {
        $value = $this->convertValueType($value);
        $this->setEnvironmentVariable($name, $value);
        $this->envVariables[$name] = $value;
    }

    public function all(): array {
        return $this->envVariables;
    }
}
*/