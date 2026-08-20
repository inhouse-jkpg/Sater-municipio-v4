<?php

declare(strict_types=1);

namespace SaterCsp;

/**
 * Mirrors Cookies and Content Security Policy header generation and adds
 * connect-src and style-src directives that the base plugin does not emit.
 */
final class ExtendedContentSecurityPolicy
{
    public static function send(): void
    {
        $policy = self::buildPolicy();
        $useMeta = (string) get_cacsp_options('cacsp_option_meta') === '1';
        $skipLegacyHeader = (string) get_cacsp_options('cacsp_option_no_x_csp') === '1';

        self::registerDebugComments($policy);
        self::registerGoogleConsentMode();

        if ($useMeta) {
            add_action(
                'wp_head',
                static function () use ($policy, $skipLegacyHeader): void {
                    if (!$skipLegacyHeader) {
                        echo '<meta http-equiv="X-Content-Security-Policy" content="' . esc_attr($policy) . '">' . "\n";
                    }

                    echo '<meta http-equiv="Content-Security-Policy" content="' . esc_attr($policy) . '">' . "\n";
                },
                1
            );

            return;
        }

        if (!$skipLegacyHeader) {
            header('X-Content-Security-Policy: ' . $policy);
        }

        header('Content-Security-Policy: ' . $policy);
    }

    public static function buildPolicy(): string
    {
        $scriptDomains = '';
        $imageDomains = '';
        $frameDomains = '';
        $formDomains = '';
        $workerDomains = '';

        $useForms = (string) get_cacsp_options('cacsp_option_forms') === '1';
        $useWorker = (string) get_cacsp_options('cacsp_option_worker') === '1';
        $cookieFilter = self::resolveCookieFilter();

        if ($cookieFilter !== null) {
            $consentCategories = json_decode($cookieFilter, true);

            if (is_array($consentCategories)) {
                foreach ($consentCategories as $category) {
                    switch ($category) {
                        case 'statistics':
                            $scriptDomains .= ' ' . get_cacsp_options('cacsp_option_statistics_scripts', true);
                            $imageDomains .= ' ' . get_cacsp_options('cacsp_option_statistics_images', true);
                            $frameDomains .= ' ' . get_cacsp_options('cacsp_option_statistics_frames', true);

                            if ($useForms) {
                                $formDomains .= ' ' . get_cacsp_options('cacsp_option_statistics_forms', true);
                            }

                            if ($useWorker) {
                                $workerDomains .= ' ' . get_cacsp_options('cacsp_option_statistics_worker', true);
                            }
                            break;

                        case 'experience':
                            $scriptDomains .= ' ' . get_cacsp_options('cacsp_option_experience_scripts', true);
                            $imageDomains .= ' ' . get_cacsp_options('cacsp_option_experience_images', true);
                            $frameDomains .= ' ' . get_cacsp_options('cacsp_option_experience_frames', true);

                            if ($useForms) {
                                $formDomains .= ' ' . get_cacsp_options('cacsp_option_experience_forms', true);
                            }

                            if ($useWorker) {
                                $workerDomains .= ' ' . get_cacsp_options('cacsp_option_experience_worker', true);
                            }
                            break;

                        case 'markerting':
                            $scriptDomains .= ' ' . get_cacsp_options('cacsp_option_markerting_scripts', true);
                            $imageDomains .= ' ' . get_cacsp_options('cacsp_option_markerting_images', true);
                            $frameDomains .= ' ' . get_cacsp_options('cacsp_option_markerting_frames', true);

                            if ($useForms) {
                                $formDomains .= ' ' . get_cacsp_options('cacsp_option_markerting_forms', true);
                            }

                            if ($useWorker) {
                                $workerDomains .= ' ' . get_cacsp_options('cacsp_option_markerting_worker', true);
                            }
                            break;
                    }
                }
            }
        }

        $blobSources = (string) get_cacsp_options('cacsp_option_blob') === '1' ? 'blob: ' : '';
        $unsafeSources = self::unsafeSources();

        $alwaysScripts = self::normalizeDomains(
            get_cacsp_options('cacsp_option_always_scripts', true) . $scriptDomains
        );
        $alwaysImages = self::normalizeDomains(
            get_cacsp_options('cacsp_option_always_images', true) . $imageDomains
        );
        $alwaysFrames = self::normalizeDomains(
            get_cacsp_options('cacsp_option_always_frames', true) . $frameDomains
        );

        $directives = [
            "script-src 'self'{$unsafeSources} {$blobSources}{$alwaysScripts};",
            "img-src 'self' data: {$blobSources}{$alwaysImages};",
            "connect-src 'self' {$blobSources}{$alwaysScripts};",
            "style-src 'self'{$unsafeSources} {$blobSources}{$alwaysScripts};",
            "object-src 'self' data: {$blobSources}{$alwaysFrames};",
            "frame-src 'self' data: {$blobSources}{$alwaysFrames};",
        ];

        if ($useForms) {
            $alwaysForms = self::normalizeDomains(
                get_cacsp_options('cacsp_option_always_forms', true) . $formDomains
            );
            $directives[] = "form-action 'self' data: {$blobSources}{$alwaysForms};";
        }

        if ($useWorker) {
            $alwaysWorker = self::normalizeDomains(
                get_cacsp_options('cacsp_option_always_worker', true) . $workerDomains
            );
            $directives[] = "worker-src 'self' data:{$unsafeSources} {$blobSources}{$alwaysWorker};";
        }

        return implode(' ', $directives);
    }

    private static function resolveCookieFilter(): ?string
    {
        if (self::shouldBypassConsent()) {
            return '["statistics","experience","markerting"]';
        }

        if (isset($_COOKIE['cookies_and_content_security_policy'])) {
            if ((string) get_cacsp_options('cacsp_option_wpengine_compatibility_mode') === '1'
                && isset($_SERVER['HTTP_X_WPENGINE_SEGMENT'])
            ) {
                return urldecode(str_replace('\\', '', (string) $_SERVER['HTTP_X_WPENGINE_SEGMENT']));
            }

            return str_replace('\\', '', (string) $_COOKIE['cookies_and_content_security_policy']);
        }

        if (isset($_SERVER['HTTP_X_WPENGINE_SEGMENT'])) {
            return urldecode(str_replace('\\', '', (string) $_SERVER['HTTP_X_WPENGINE_SEGMENT']));
        }

        return null;
    }

    private static function shouldBypassConsent(): bool
    {
        if (isset($_GET['cacsp_bypass'])) {
            setcookie('cookies_and_content_security_policy', '["statistics","experience","markerting"]');

            return true;
        }

        if ((string) get_cacsp_options('cacsp_option_bypass_ip') !== '1') {
            return false;
        }

        $bypassIps = array_map(
            'trim',
            explode("\n", rtrim((string) get_cacsp_options('cacsp_option_bypass_ips')))
        );

        return in_array($_SERVER['REMOTE_ADDR'] ?? '', $bypassIps, true);
    }

    private static function unsafeSources(): string
    {
        $unsafe = '';

        if ((string) get_cacsp_options('cacsp_option_disable_unsafe_inline') !== '1') {
            $unsafe .= " 'unsafe-inline'";
        }

        if ((string) get_cacsp_options('cacsp_option_disable_unsafe_eval') !== '1') {
            $unsafe .= " 'unsafe-eval'";
        }

        return $unsafe;
    }

    private static function normalizeDomains(string $domains): string
    {
        if (!function_exists('cacsp_single_space')) {
            return preg_replace('/\s+/', ' ', trim($domains)) ?? '';
        }

        return cacsp_single_space($domains);
    }

    private static function registerDebugComments(string $policy): void
    {
        if ((string) get_cacsp_options('cacsp_option_debug') !== '1') {
            return;
        }

        add_action(
            'wp_head',
            static function () use ($policy): void {
                echo "<!-- Setting Content Security Policy (Sater CSP Extensions) -->\n";
                echo "<!-- Content Security Policy Cookie settings:\n"
                    . esc_html(str_replace('; ', ";\n", $policy))
                    . " -->\n";
            },
            0
        );
    }

    private static function registerGoogleConsentMode(): void
    {
        if ((string) get_cacsp_options('cacsp_option_google_consent_mode', false, '0') !== '1') {
            return;
        }

        add_action(
            'wp_head',
            static function (): void {
                echo "<script id=\"cacsp-gtag-consent-default\">
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent', 'default', {
    'ad_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied',
    'analytics_storage': 'denied',
    'functionality_storage': 'denied',
    'personalization_storage': 'denied',
    'security_storage': 'denied',
    'wait_for_update': 500
});
</script>\n";
            },
            0
        );
    }
}
