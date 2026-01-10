<?php
class EnvLoader {
    public static function load($path) {
        if (!file_exists($path)) {
            // It's okay if .env doesn't exist in production if vars are set via server config
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse line
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remove quotes if they exist
                if (strpos($value, '"') === 0 && substr($value, -1) === '"') {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && substr($value, -1) === "'") {
                    $value = substr($value, 1, -1);
                }

                // Set environment variable if not already set
                if (!getenv($name)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}
?>
