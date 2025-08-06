<?php

namespace App\Services;

class VisitorCounterService
{
    private string $dataFile;
    
    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../../storage/visitors.json';
        $this->ensureStorageExists();
    }
    
    public function recordVisitor(): array
    {
        $ip = $this->getClientIP();
        $country = $this->getCountryFromIP($ip);
        
        if ($country) {
            $this->incrementCounter($country);
        }
        
        return $this->getVisitorStats();
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
    
    private function getCountryFromIP(string $ip): ?array
    {
        // Skip localhost/private IPs
        if ($ip === '127.0.0.1' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            return ['code' => 'LOCAL', 'name' => 'Local', 'flag' => '🏠'];
        }
        
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
}