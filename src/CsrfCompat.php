<?php

/**
 * oe-module-coverage-latam — CsrfCompat
 *
 * Helper CSRF retro-compatible con las distintas firmas de
 * OpenEMR\Common\Csrf\CsrfUtils::collectCsrfToken() según la versión de OpenEMR.
 *
 * En ciertas versiones el parámetro SessionInterface pasó a ser obligatorio en
 * collectCsrfToken()/verifyCsrfToken(), rompiendo las llamadas sin argumentos.
 * Este helper evita esa incompatibilidad leyendo la clave privada directamente
 * de $_SESSION (funciona en todas las versiones) y calculando el token con el
 * mismo algoritmo que usa el núcleo (hash_hmac sha256, truncado a 40 chars).
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\CoverageLatam;

class CsrfCompat
{
    public static function collectCsrfToken(string $subject = 'default'): string
    {
        $privateKey = $_SESSION['csrf_private_key'] ?? null;
        if (empty($privateKey)) {
            return '';
        }
        return substr(hash_hmac('sha256', $subject, (string) $privateKey), 0, 40);
    }

    public static function verifyCsrfToken(string $token, string $subject = 'default'): bool
    {
        $expected = self::collectCsrfToken($subject);
        if (empty($expected) || empty($token)) {
            return false;
        }
        return hash_equals($expected, $token);
    }
}
