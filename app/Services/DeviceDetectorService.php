<?php

namespace App\Services;

use Jenssegers\Agent\Agent;

class DeviceDetectorService
{
    /**
     * Parses the user agent string to extract device type, browser, os, and specific device name.
     * Uses best-effort approach to extract Android models and PC identifiers without aggressive fingerprinting.
     *
     * @param string $userAgent
     * @return array
     */
    public static function detect($userAgent)
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        $platform = $agent->platform(); // e.g. Windows, OS X, Android, iOS
        $browser = $agent->browser(); // e.g. Chrome, Safari
        $browserVersion = $agent->version($browser);
        $device = $agent->device(); // e.g. SM-S918B, iPhone, WebKit

        $deviceName = null;
        $deviceType = $agent->isDesktop() ? 'Desktop' : ($agent->isTablet() ? 'Tablet' : 'Mobile');
        $osVersion = $agent->version($platform);
        $os = $platform ? trim($platform . ' ' . $osVersion) : null;

        if ($agent->isDesktop()) {
            if ($platform === 'Windows') {
                $deviceName = 'Windows PC';
            } elseif ($platform === 'OS X') {
                $deviceName = 'Mac';
            } elseif ($platform === 'Linux') {
                $deviceName = 'Linux PC';
            } else {
                $deviceName = $platform ? $platform . ' PC' : 'Desktop PC';
            }
        } elseif ($agent->isPhone() || $agent->isTablet()) {
            if ($platform === 'iOS') {
                $deviceName = $agent->isTablet() ? 'iPad' : 'iPhone';
            } elseif ($platform === 'Android') {
                // Try to extract Android model carefully from User Agent.
                // Typical format: Mozilla/5.0 (Linux; Android 13; SM-S918B Build/xxx)
                if (preg_match('/Android\s+[0-9\.]+(?:\s*;([^;)]+))+\s+Build/i', $userAgent, $matches)) {
                    $possibleModel = trim(last(explode(';', $matches[1])));
                    if (!in_array(strtolower($possibleModel), ['wv', 'mobile', 'tablet', 'linux', 'android'])) {
                        $deviceName = $possibleModel;
                    }
                }
                
                // Regex 2 for User Agents without 'Build/'
                if (!$deviceName && preg_match('/Android\s+[0-9\.]+;\s+([^;)]+)\)/i', $userAgent, $matches)) {
                    $possibleModel = trim(last(explode(';', $matches[1])));
                     if (!in_array(strtolower($possibleModel), ['wv', 'mobile', 'tablet', 'linux', 'android'])) {
                        $deviceName = $possibleModel;
                    }
                }

                // Fallback to agent->device() if regex fails and device is not a generic engine name
                if (!$deviceName && $device && !in_array(strtolower($device), ['webkit', 'gecko', 'applewebkit'])) {
                    $deviceName = $device;
                }

                // Mapping generic identifier
                if (preg_match('/^RMX[0-9]+/', $deviceName)) {
                    $deviceName = 'Realme ' . $deviceName;
                } elseif (preg_match('/^SM-[A-Z0-9]+/', $deviceName)) {
                    $deviceName = 'Samsung ' . $deviceName;
                } elseif (preg_match('/^CPH[0-9]+/', $deviceName)) {
                    $deviceName = 'Oppo ' . $deviceName;
                } elseif (preg_match('/^V[0-9]+/', $deviceName)) {
                    $deviceName = 'Vivo ' . $deviceName;
                } elseif (preg_match('/^M[0-9A-Z]+/', $deviceName)) {
                    $deviceName = 'Poco/Xiaomi ' . $deviceName;
                }

                // Final fallback
                if (!$deviceName) {
                    $deviceName = $agent->isTablet() ? 'Android Tablet' : 'Android Phone';
                }
            }
        }
        
        // Final fallback if everything fails
        if (!$deviceName) {
            if ($device && !in_array(strtolower($device), ['webkit', 'gecko', 'applewebkit'])) {
                $deviceName = $device;
            } else {
                $deviceName = 'Perangkat Tidak Dikenal';
            }
        }

        return [
            'device_type' => $deviceType,
            'browser'     => $browser ? trim($browser . ' ' . $browserVersion) : 'Browser Tidak Dikenal',
            'os'          => $os ?: 'OS Tidak Dikenal',
            'device_name' => $deviceName,
            'raw_agent'   => $userAgent,
        ];
    }
}
