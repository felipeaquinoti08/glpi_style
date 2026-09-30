<?php

namespace GlpiPlugin\Glpistyle\Ui;

/**
 * Lists the interface improvements found in src/Ui/*Feature.php.
 */
final class Registry
{
    /** @var array<string, class-string<Feature>>|null key => class */
    private static ?array $features = null;

    /**
     * @return array<string, class-string<Feature>>
     */
    public static function all(): array
    {
        if (self::$features === null) {
            $features = [];
            foreach (glob(__DIR__ . '/*Feature.php') ?: [] as $file) {
                $class = __NAMESPACE__ . '\\' . basename($file, '.php');
                if ($class === Feature::class || !is_subclass_of($class, Feature::class)) {
                    continue;
                }
                $features[$class::key()] = $class;
            }
            uasort($features, static fn($a, $b) => $a::order() <=> $b::order());
            self::$features = $features;
        }
        return self::$features;
    }

    /**
     * @return array<string, class-string<Feature>>
     */
    public static function enabled(array $config): array
    {
        return array_filter(self::all(), static fn($feature) => $feature::isEnabled($config));
    }

    public static function schema(): array
    {
        $schema = [];
        foreach (self::all() as $feature) {
            $schema += $feature::schema();
        }
        return $schema;
    }

    /** @return class-string<Feature>|null */
    public static function get(string $key): ?string
    {
        return self::all()[$key] ?? null;
    }
}
