<?php

/**
 * oe-module-coverage-latam — CsrfCompat
 *
 * Helper CSRF retro-compatible con las distintas firmas de
 * OpenEMR\Common\Csrf\CsrfUtils::collectCsrfToken() según la versión de OpenEMR.
 *
 * En ciertas versiones el parámetro SessionInterface pasó a ser obligatorio en
 * collectCsrfToken()/verifyCsrfToken(), rompiendo las llamadas sin argumentos.
 * Para evitarlo, este helper:
 *   1. Intenta usar el CsrfUtils nativo de OpenEMR (firma estática) si está
 *      disponible; ante cualquier incompatibilidad de firma cae al punto 2.
 *   2. Como fallback, lee la clave privada directamente de la sesión y calcula
 *      el token con el mismo algoritmo del núcleo (hash_hmac sha256, truncado
 *      a 40 chars). La clave se busca tanto en $_SESSION (acceso directo)
 *      como en el storage de la sesión Symfony ($_SESSION['_sf2_attributes']),
 *      para cubrir las distintas formas en que OpenEMR guarda csrf_private_key.
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
        // Intento 1: CsrfUtils nativo de OpenEMR (todas las firmas)
        $token = self::tryNative('collectCsrfToken', $subject);
        if ($token !== '') {
            return $token;
        }

        // Intento 2: fallback manual con la clave privada de la sesión
        $privateKey = self::sessionPrivateKey();
        if ($privateKey === '') {
            return '';
        }
        return substr(hash_hmac('sha256', $subject, $privateKey), 0, 40);
    }

    public static function verifyCsrfToken(string $token, string $subject = 'default'): bool
    {
        $expected = self::collectCsrfToken($subject);
        if ($expected === '' || $token === '') {
            return false;
        }
        return hash_equals($expected, $token);
    }

    /**
     * Invoca un método estático de OpenEMR\Common\Csrf\CsrfUtils tolerando
     * las distintas firmas (1 o 2 parámetros) y la ausencia de la clase.
     *
     * @param array<int, mixed> $args
     */
    private static function tryNative(string $method, ...$args): string
    {
        $class = 'OpenEMR\\Common\\Csrf\\CsrfUtils';
        if (!class_exists($class) || !method_exists($class, $method)) {
            return '';
        }
        try {
            $result = $class::$method(...$args);
            return is_string($result) ? $result : '';
        } catch (\Throwable $e) {
            // Firma incompatible o error de inicialización → fallback manual
            return '';
        }
    }

    /**
     * Devuelve la clave privada CSRF de la sesión, en cualquiera de los
     * formatos que usa OpenEMR (acceso directo o storage de Symfony Session).
     */
    private static function sessionPrivateKey(): string
    {
        $raw = $_SESSION['csrf_private_key'] ?? null;
        if (empty($raw)) {
            // Symfony Session guarda los atributos bajo _sf2_attributes
            $sf2 = $_SESSION['_sf2_attributes'] ?? null;
            if (is_array($sf2)) {
                $raw = $sf2['csrf_private_key'] ?? null;
            }
        }
        if (empty($raw)) {
            return '';
        }
        if (is_object($raw) && method_exists($raw, '__toString')) {
            return (string) $raw;
        }
        return is_string($raw) ? $raw : '';
    }
}
