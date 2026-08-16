<?php

/**
 * oe-module-coverage-latam — CountryPackInstaller
 *
 * Instala un paquete de país (definido en packs/*.json) delegando en
 * CountryPackImporter, que es la implementación canónica del proceso:
 * registro del nomenclador, upsert del paquete y de las reglas de
 * autorización/frecuencia y de las equivalencias de códigos, todo dentro
 * de una transacción (idempotente, con ON DUPLICATE KEY UPDATE).
 *
 * Esta clase se conserva como capa de compatibilidad para el flujo de
 * instalación existente (pages/api/country_packs.php).
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

/**
 * Aplica un paquete de país sobre la base de datos del módulo de forma idempotente.
 */
class CountryPackInstaller
{
    /**
     * Instala (o actualiza) un paquete de país.
     *
     * @param array<string, mixed> $pack Definición del paquete (contenido del JSON).
     * @return array<string, mixed> Resumen de lo instalado.
     * @throws \RuntimeException Si el paquete es inválido o el import falla.
     */
    public function install(array $pack): array
    {
        return (new CountryPackImporter())->import($pack);
    }
}
