<?php

/**
 * oe-module-coverage-latam — AdapterRegistry
 *
 * Registro centralizado de adaptadores de integración.
 * Resuelve qué adaptador usar para cada financiador, con fallback al adaptador manual.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

use OpenEMR\Modules\CoverageLatam\Adapter\ManualFallbackAdapter;
use OpenEMR\Modules\CoverageLatam\Contracts\CoverageAdapterInterface;

/**
 * Registro de adaptadores plugables por financiador.
 *
 * Cada financiador puede tener un adaptador registrado en covl_adapters.
 * Si no hay adaptador específico o no está disponible, se usa ManualFallbackAdapter.
 */
class AdapterRegistry
{
    /** @var array<string, CoverageAdapterInterface> Adaptadores instanciados, clave por adapter_key */
    private array $instances = [];

    public function __construct(
        private readonly \Closure $dbQuery,
    ) {
        // El adaptador manual siempre está disponible
        $this->register(new ManualFallbackAdapter());
    }

    /**
     * Registra un adaptador en el registry.
     */
    public function register(CoverageAdapterInterface $adapter): void
    {
        $this->instances[$adapter->getAdapterKey()] = $adapter;
    }

    /**
     * Retorna el adaptador apropiado para un financiador dado.
     * Si no hay adaptador específico configurado, retorna el adaptador manual.
     *
     * @param int $insCompanyId ID del financiador (insurance_companies.id)
     */
    public function getForInsurer(int $insCompanyId): CoverageAdapterInterface
    {
        // Buscar en covl_adapters el adaptador activo para este financiador
        $sql = "SELECT adapter_key, php_class, config_json
                FROM covl_adapters
                WHERE active = 1
                  AND insurance_company_id = ?
                ORDER BY id DESC
                LIMIT 1";

        $row = ($this->dbQuery)($sql, [$insCompanyId]);

        if ($row !== null) {
            $adapterKey = $row['adapter_key'];

            // Si ya está instanciado, retornarlo directamente
            if (isset($this->instances[$adapterKey]) && $this->instances[$adapterKey]->isAvailable()) {
                return $this->instances[$adapterKey];
            }

            // Intentar instanciar dinámicamente la clase PHP configurada
            $phpClass = $row['php_class'];
            if (class_exists($phpClass)) {
                $config  = json_decode($row['config_json'] ?? '{}', true) ?: [];
                $adapter = new $phpClass($config);
                if ($adapter instanceof CoverageAdapterInterface && $adapter->isAvailable()) {
                    $this->register($adapter);
                    return $adapter;
                }
            }
        }

        // Fallback: adaptador manual
        return $this->instances[ManualFallbackAdapter::ADAPTER_KEY];
    }

    /**
     * Retorna un adaptador por su clave única.
     */
    public function getByKey(string $adapterKey): CoverageAdapterInterface
    {
        return $this->instances[$adapterKey] ?? $this->instances[ManualFallbackAdapter::ADAPTER_KEY];
    }
}
