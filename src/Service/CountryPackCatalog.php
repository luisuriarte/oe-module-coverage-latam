<?php

/**
 * oe-module-coverage-latam — CountryPackCatalog
 *
 * Catálogo de paquetes de país disponibles en el módulo (packs/*.json).
 * Cada paquete define el nomenclador nacional (tipo de código), reglas
 * base de autorización/frecuencia y equivalencias de códigos a estándares.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

/**
 * Lee el catálogo de paquetes de país desde la carpeta packs/ del módulo.
 */
class CountryPackCatalog
{
    /** @var string|null Directorio del catálogo (resuelto en el primer acceso) */
    private ?string $packDir = null;

    /**
     * Retorna la lista completa de paquetes disponibles (contenido de packs/*.json).
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $packs = [];
        foreach (glob($this->dir() . '/*.json') ?: [] as $file) {
            $contents = json_decode((string) file_get_contents($file), true);
            if (is_array($contents) && isset($contents['country_code'])) {
                $packs[] = $contents;
            }
        }
        usort($packs, static fn (array $a, array $b): int => strcmp($a['country_code'], $b['country_code']));
        return $packs;
    }

    /**
     * Retorna un paquete por código de país ISO 3166-1 alpha-2 (ej: 'AR').
     *
     * @return array<string, mixed>|null
     */
    public function get(string $countryCode): ?array
    {
        $code = strtoupper(trim($countryCode));
        $file = $this->dir() . '/' . strtolower($code) . '.json';
        if (!is_file($file)) {
            return null;
        }
        $contents = json_decode((string) file_get_contents($file), true);
        if (is_array($contents)) {
            error_log("[covl] CountryPackCatalog::get({$countryCode}): file={$file}, auth_rules=" . count($contents['auth_rules'] ?? []) . ", freq_rules=" . count($contents['frequency_rules'] ?? []) . ", code_maps=" . count($contents['code_maps'] ?? []));
        }
        return is_array($contents) ? $contents : null;
    }

    /**
     * Directorio packs/ del módulo (raíz del módulo + /packs).
     */
    private function dir(): string
    {
        if ($this->packDir === null) {
            $this->packDir = dirname(__DIR__, 2) . '/packs';
        }
        return $this->packDir;
    }
}
