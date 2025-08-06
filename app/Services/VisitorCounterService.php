<?php

namespace App\Services;

class VisitorCounterService
{
    private string $dataFile;
    private string $sessionFile;
    
    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../../storage/visitors.json';
        $this->sessionFile = __DIR__ . '/../../storage/visitor_sessions.json';
        $this->ensureStorageExists();
    }
    
    public function recordVisitor(): array
    {
        $ip = $this->getClientIP();
        
        // Skip local/private IPs entirely - don't process them at all
        if ($this->isLocalIP($ip)) {
            return $this->getVisitorStats();
        }
        
        // Check if this IP was already counted in this session or recently
        if ($this->isUniqueVisitor($ip)) {
            $country = $this->getCountryFromIP($ip);
            
            if ($country) {
                $this->incrementCounter($country);
                $this->markVisitorAsCounted($ip);
            }
        }
        
        return $this->getVisitorStats();
    }
    
    private function isUniqueVisitor(string $ip): bool
    {
        // Check session first - if already counted in this session, skip
        if (isset($_SESSION['visitor_counted']) && $_SESSION['visitor_counted'] === true) {
            return false;
        }
        
        // Check if IP was counted recently (within last 24 hours)
        $sessions = $this->getVisitorSessions();
        $currentTime = time();
        $dayInSeconds = 24 * 60 * 60; // 24 hours
        
        if (isset($sessions[$ip])) {
            $lastVisit = $sessions[$ip]['last_counted'];
            
            // If last visit was within 24 hours, don't count again
            if (($currentTime - $lastVisit) < $dayInSeconds) {
                return false;
            }
        }
        
        return true;
    }
    
    private function markVisitorAsCounted(string $ip): void
    {
        // Mark in session
        $_SESSION['visitor_counted'] = true;
        
        // Update IP tracking file
        $sessions = $this->getVisitorSessions();
        $sessions[$ip] = [
            'last_counted' => time(),
            'count' => ($sessions[$ip]['count'] ?? 0) + 1
        ];
        
        // Clean old entries (older than 30 days)
        $this->cleanOldSessions($sessions);
        
        file_put_contents($this->sessionFile, json_encode($sessions, JSON_PRETTY_PRINT));
    }
    
    private function getVisitorSessions(): array
    {
        if (!file_exists($this->sessionFile)) {
            return [];
        }
        
        $data = json_decode(file_get_contents($this->sessionFile), true);
        return $data ?: [];
    }
    
    private function cleanOldSessions(array &$sessions): void
    {
        $currentTime = time();
        $monthInSeconds = 30 * 24 * 60 * 60; // 30 days
        
        foreach ($sessions as $ip => $data) {
            if (($currentTime - $data['last_counted']) > $monthInSeconds) {
                unset($sessions[$ip]);
            }
        }
    }
    
    public function getVisitorStats(): array
    {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        
        $data = json_decode(file_get_contents($this->dataFile), true);
        return $data ?: [];
    }
    
    private function getClientIP(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    private function isLocalIP(string $ip): bool
    {
        // Check for localhost and private IP ranges
        return $ip === '127.0.0.1' || 
               $ip === '::1' || 
               !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
    
    private function getCountryFromIP(string $ip): ?array
    {
        try {
            // Using ip-api.com (free, no key required, 1000 requests/month)
            $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'user_agent' => 'PvP Calculator Visitor Counter'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && $data['status'] === 'success') {
                    return [
                        'code' => $data['countryCode'],
                        'name' => $data['country'],
                        'flag' => $this->getCountryFlag($data['countryCode'])
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log('Geolocation error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    private function getCountryFlag(string $countryCode): string
    {
        // Convert country code to flag emoji
        $countryCode = strtoupper($countryCode);
        
        // Special cases
        $specialFlags = [
            'LOCAL' => '🏠',
            'UNKNOWN' => '🌍'
        ];
        
        if (isset($specialFlags[$countryCode])) {
            return $specialFlags[$countryCode];
        }
        
        // Convert to regional indicator symbols (flag emojis)
        if (strlen($countryCode) === 2) {
            $flag = '';
            for ($i = 0; $i < 2; $i++) {
                $flag .= mb_chr(ord($countryCode[$i]) - ord('A') + 0x1F1E6, 'UTF-8');
            }
            return $flag;
        }
        
        return '🌍';
    }
    
    private function incrementCounter(array $country): void
    {
        $data = $this->getVisitorStats();
        
        $countryKey = $country['code'];
        
        if (!isset($data[$countryKey])) {
            $data[$countryKey] = [
                'name' => $country['name'],
                'flag' => $country['flag'],
                'count' => 0,
                'first_visit' => date('Y-m-d H:i:s'),
                'last_visit' => date('Y-m-d H:i:s')
            ];
        }
        
        $data[$countryKey]['count']++;
        $data[$countryKey]['last_visit'] = date('Y-m-d H:i:s');
        
        // Sort by count (descending)
        uasort($data, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    private function ensureStorageExists(): void
    {
        $storageDir = dirname($this->dataFile);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, '{}');
        }
        
        if (!file_exists($this->sessionFile)) {
            file_put_contents($this->sessionFile, '{}');
        }
    }
    
    public function getTotalVisitors(): int
    {
        $data = $this->getVisitorStats();
        return array_sum(array_column($data, 'count'));
    }
    
    public function getTopCountries(int $limit = 10): array
    {
        $data = $this->getVisitorStats();
        return array_slice($data, 0, $limit, true);
    }
    
    // Debug method to check visitor status
    public function getVisitorStatus(): array
    {
        $ip = $this->getClientIP();
        $sessions = $this->getVisitorSessions();
        
        return [
            'ip' => $ip,
            'session_counted' => $_SESSION['visitor_counted'] ?? false,
            'ip_last_counted' => $sessions[$ip]['last_counted'] ?? null,
            'is_unique' => $this->isUniqueVisitor($ip)
        ];
    }
}