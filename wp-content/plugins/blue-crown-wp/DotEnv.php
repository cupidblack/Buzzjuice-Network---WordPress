<?php
class DotEnv {
    protected $path;

    public function __construct(string $path) {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('The .env file at %s does not exist', $path));
        }
        $this->path = $path;
    }

    public function load(): void {
        if (!is_readable($this->path)) {
            throw new \RuntimeException(sprintf('The .env file at %s is not readable', $this->path));
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (strpos($line, '#') === 0 || empty($line)) {
                continue;
            }

            // Handle "export KEY=VALUE" syntax
            if (strpos($line, 'export ') === 0) {
                $line = substr($line, 7);
            }

            // Parse key-value pair
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = $this->sanitizeValue(trim($value));

                // Set environment variables
                $this->setEnvironmentVariable($name, $value);
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

    protected function setEnvironmentVariable(string $name, string $value): void {
        // Set the environment variable
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}