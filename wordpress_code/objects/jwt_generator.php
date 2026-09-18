<?php
/**
 * Classe utilitaire pour générer/vérifier des JWT (HS256)
 * Sans dépendance externe
 */

defined('ABSPATH') || exit;

class AssoSimpleJWT {

    /**
     * Récupère (ou génère) le secret utilisé pour signer les tokens.
     * On le stocke dans les options WP pour qu'il soit stable entre les requêtes.
     */
    private static function get_secret() {
        if (defined('MON_PLUGIN_JWT_SECRET') && MON_PLUGIN_JWT_SECRET) {
            return MON_PLUGIN_JWT_SECRET;
        }

        return null;
    }

    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Génère un JWT signé.
     * @param array $payload  Les données à encoder (ex: ['user_id' => 5])
     * @param int   $ttl      Durée de validité en secondes (par défaut 1h)
     */
    public static function generate($payload, $ttl = 3600) {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;

        $header_encoded  = self::base64url_encode(json_encode($header));
        $payload_encoded = self::base64url_encode(json_encode($payload));

        $secret = self::get_secret();

        if(!$secret) {
            throw new Exception('JWT secret is not available.');
        }

        $signature = hash_hmac(
            'sha256',
            $header_encoded . '.' . $payload_encoded,
            $secret,
            true
        );
        $signature_encoded = self::base64url_encode($signature);

        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }

    /**
     * Vérifie un JWT et retourne le payload décodé, ou false si invalide/expiré.
     */
    public static function verify($jwt) {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return false;
        }

        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;

        // Recalcule la signature attendue
        $expected_signature = hash_hmac(
            'sha256',
            $header_encoded . '.' . $payload_encoded,
            self::get_secret(),
            true
        );
        $expected_signature_encoded = self::base64url_encode($expected_signature);

        // Comparaison en temps constant (anti timing-attack)
        if (!hash_equals($expected_signature_encoded, $signature_encoded)) {
            return false;
        }

        $payload = json_decode(self::base64url_decode($payload_encoded), true);

        if (!$payload) {
            return false;
        }

        // Vérifie l'expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}