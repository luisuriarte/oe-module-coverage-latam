<?php

/**
 * oe-module-coverage-latam — CsrfCompat
 *
 * Helper CSRF retro-compatible con las distintas firmas de
 * OpenEMR\Common\Csrf\CsrfUtils según la versión de OpenEMR:
 *
 *   - OpenEMR 8.2.x (incl. fork OpenCoreEMR):
 *       collectCsrfToken(SessionInterface $session, string $subject = 'default')
 *       verifyCsrfToken($token, SessionInterface $session, string $subject = 'default')
 *     La clave privada vive en la sesión Symfony ($session->get('csrf_private_key')).
 *
 *   - OpenEMR 8.0.x:
 *       collectCsrfToken($subject = 'default', ?SessionInterface $session = null)
 *       verifyCsrfToken($token, $subject = 'default', ?SessionInterface $session = null)
 *     La clave privada vive en $_SESSION['csrf_private_key'].
 *
 * Para cubrir ambas versiones, este helper:
 *   1. Obtiene la sesión activa (getActiveSession en 8.2 / getWrapper en 8.0).
 *   2. Intenta CsrfUtils nativo con (session, subject) y luego con (subject).
 *   3. Como fallback, lee la clave privada de la sesión (objeto o $_SESSION)
 *      y calcula el token con el mismo algoritmo del núcleo (hash_hmac sha256,
 *      truncado a 40 chars).
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\CoverageLatam;

use OpenEMR\Common\Session\SessionWrapperFactory;

class CsrfCompat
{
    public static function collectCsrfToken(string $subject = 'default'): string
    {
        $session = self::getActiveSession();

        // Intento 1: CsrfUtils nativo con sesión activa (firma 8.2+)
        if ($session !== null) {
            $token = self::tryNative('collectCsrfToken', $session, $subject);
            if ($token !== '') {
                return $token;
            }
        }

        // Intento 2: CsrfUtils nativo sin sesión (firma 8.0)
        $token = self::tryNative('collectCsrfToken', $subject);
        if ($token !== '') {
            return $token;
        }

        // Intento 3: fallback manual con la clave privada de la sesión
        $privateKey = self::sessionPrivateKey($session);
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
     * Devuelve la sesión activa (objeto con get/set) si existe:
     *   - 8.2+: SessionWrapperFactory::getActiveSession() (Symfony SessionInterface)
     *   - 8.0:  SessionWrapperFactory::getWrapper() (PHPSessionWrapper)
     */
    private static function getActiveSession(): ?object
    {
        if (!class_exists(SessionWrapperFactory::class)) {
            return null;
        }
        try {
            $factory = SessionWrapperFactory::getInstance();
            $session = null;
            if (method_exists($factory, 'getActiveSession')) {
                $session = $factory->getActiveSession();
            } elseif (method_exists($factory, 'getWrapper')) {
                $session = $factory->getWrapper();
            }
            if (is_object($session) && method_exists($session, 'get')) {
                return $session;
            }
        } catch (\Throwable $e) {
            // Sin sesión disponible → se continúa sin ella
        }
        return null;
    }

    /**
     * Invoca un método estático de OpenEMR\Common\Csrf\CsrfUtils tolerando
     * las distintas firmas y la ausencia de la clase.
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
     * Devuelve la clave privada CSRF desde cualquiera de las ubicaciones
     * usadas por OpenEMR (sesión Symfony, $_SESSION directo o bag _sf2_attributes).
     */
    private static function sessionPrivateKey(?object $session): string
    {
        $raw = null;

        if ($session !== null && method_exists($session, 'get')) {
            $raw = $session->get('csrf_private_key', null);
        }
        if (empty($raw)) {
            $raw = $_SESSION['csrf_private_key'] ?? null;
        }
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
