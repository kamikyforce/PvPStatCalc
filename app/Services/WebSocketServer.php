<?php

namespace App\Services;

use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\Stream\WritableResourceStream;
use React\EventLoop\Loop;

class WebSocketServer
{
    protected $clients;
    protected $visitorService;
    protected $rooms;
    protected $userSessions;
    protected $loop;
    
    public function __construct()
    {
        $this->clients = [];
        $this->visitorService = new VisitorCounterService();
        $this->rooms = [];
        $this->userSessions = [];
        $this->loop = Loop::get();
        
        echo "🚀 WebSocket Server initialized\n";
    }
    
    public function startServer($port = 8080) {
        // Use environment PORT if available (Railway)
        $port = $_ENV['PORT'] ?? $port;
        
        echo "Starting WebSocket server on port {$port}...\n";
        
        $loop = Loop::get();
        $socket = new SocketServer("0.0.0.0:{$port}", $loop);
        
        $server = new HttpServer($loop, function (ServerRequestInterface $request) {
            // Handle WebSocket upgrade
            if ($request->getHeaderLine('Upgrade') === 'websocket') {
                return $this->handleWebSocketUpgrade($request);
            }
            
            // Handle regular HTTP requests (fallback)
            return new Response(200, ['Content-Type' => 'text/plain'], 'WebSocket Server Running');
        });
        
        $server->listen($socket);
        
        echo "WebSocket server started on ws://0.0.0.0:{$port}\n";
        $loop->run();
    }
    
    private function handleWebSocketUpgrade(ServerRequestInterface $request)
    {
        $headers = $request->getHeaders();
        $key = $headers['sec-websocket-key'][0] ?? '';
        
        if (empty($key)) {
            return new Response(400, [], 'Bad Request');
        }
        
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        
        return new Response(101, [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $acceptKey,
        ]);
    }
    
    public function onConnection($connection)
    {
        $sessionId = uniqid('ws_', true);
        $ip = $this->getClientIP($connection);
        $country = $this->getCountryFromIP($ip);
        
        $clientData = [
            'connection' => $connection,
            'sessionId' => $sessionId,
            'ip' => $ip,
            'country' => $country,
            'lastSeen' => time(),
            'isActive' => true
        ];
        
        $this->clients[$sessionId] = $clientData;
        $this->userSessions[$sessionId] = $clientData;
        
        // Update online status
        $this->visitorService->updateOnlineStatus();
        
        // Send welcome message
        $this->sendToClient($sessionId, [
            'type' => 'connection_established',
            'sessionId' => $sessionId,
            'country' => $country,
            'timestamp' => time()
        ]);
        
        // Broadcast updated visitor count
        $this->broadcastVisitorUpdate();
        
        echo "🟢 New connection: {$ip} ({$country['name']}) - Session: {$sessionId}\n";
        
        // Handle connection close
        $connection->on('close', function() use ($sessionId) {
            $this->onClose($sessionId);
        });
        
        // Handle incoming messages
        $connection->on('data', function($data) use ($sessionId) {
            $this->onMessage($sessionId, $data);
        });
    }
    
    public function onMessage($sessionId, $msg)
    {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['type'])) {
            return;
        }
        
        // Update last seen
        if (isset($this->clients[$sessionId])) {
            $this->clients[$sessionId]['lastSeen'] = time();
        }
        
        switch ($data['type']) {
            case 'heartbeat':
                $this->handleHeartbeat($sessionId, $data);
                break;
                
            case 'user_activity':
                $this->handleUserActivity($sessionId, $data);
                break;
                
            case 'page_view':
                $this->handlePageView($sessionId, $data);
                break;
                
            case 'calculator_usage':
                $this->handleCalculatorUsage($sessionId, $data);
                break;
                
            case 'join_room':
                $this->handleJoinRoom($sessionId, $data);
                break;
                
            default:
                echo "⚠️ Unknown message type: {$data['type']}\n";
        }
    }
    
    public function onClose($sessionId)
    {
        if (isset($this->clients[$sessionId])) {
            $client = $this->clients[$sessionId];
            unset($this->clients[$sessionId]);
            unset($this->userSessions[$sessionId]);
            
            // Update online status
            $this->visitorService->updateOnlineStatus();
            
            // Broadcast updated count
            $this->broadcastVisitorUpdate();
            
            $country = $client['country']['name'] ?? 'Unknown';
            echo "🔴 Connection closed: {$client['ip']} ({$country})\n";
        }
    }
    
    private function handleHeartbeat($sessionId, array $data)
    {
        $this->sendToClient($sessionId, [
            'type' => 'heartbeat_ack',
            'timestamp' => time(),
            'latency' => isset($data['timestamp']) ? (time() - $data['timestamp']) : 0
        ]);
    }
    
    private function handleUserActivity($sessionId, array $data)
    {
        if (!isset($this->clients[$sessionId])) return;
        
        $client = $this->clients[$sessionId];
        $activity = [
            'sessionId' => $sessionId,
            'activity' => $data['activity'] ?? 'unknown',
            'timestamp' => time(),
            'country' => $client['country']
        ];
        
        // Broadcast activity to other users
        $this->broadcastToOthers($sessionId, [
            'type' => 'user_activity_update',
            'data' => $activity
        ]);
    }
    
    private function handlePageView($sessionId, array $data)
    {
        // Record page view in visitor service
        $this->recordPageView($data['page'] ?? 'unknown');
        $this->broadcastVisitorUpdate();
    }
    
    private function handleCalculatorUsage($sessionId, array $data)
    {
        if (!isset($this->clients[$sessionId])) return;
        
        $client = $this->clients[$sessionId];
        $usage = [
            'sessionId' => $sessionId,
            'calculation_type' => $data['calculation_type'] ?? 'unknown',
            'timestamp' => time(),
            'country' => $client['country']
        ];
        
        // Broadcast calculator usage to all users
        $this->broadcastToAll([
            'type' => 'calculator_usage_update',
            'data' => $usage
        ]);
    }
    
    private function handleJoinRoom($sessionId, array $data)
    {
        $room = $data['room'] ?? 'general';
        
        if (!isset($this->rooms[$room])) {
            $this->rooms[$room] = [];
        }
        
        $this->rooms[$room][] = $sessionId;
        
        $this->sendToClient($sessionId, [
            'type' => 'room_joined',
            'room' => $room,
            'timestamp' => time()
        ]);
    }
    
    private function broadcastVisitorUpdate()
    {
        $data = [
            'type' => 'visitor_update',
            'visitor_stats' => $this->visitorService->getVisitorStats(),
            'online_countries' => $this->getOnlineCountries(),
            'timestamp' => time()
        ];
        
        $this->broadcastToAll($data);
    }
    
    private function getOnlineCountries(): array
    {
        $countries = [];
        
        foreach ($this->clients as $client) {
            if (isset($client['country'])) {
                $countryCode = $client['country']['code'];
                if (!isset($countries[$countryCode])) {
                    $countries[$countryCode] = [
                        'name' => $client['country']['name'],
                        'flag' => $client['country']['flag'],
                        'online_count' => 0
                    ];
                }
                $countries[$countryCode]['online_count']++;
            }
        }
        
        return $countries;
    }
    
    private function sendToClient($sessionId, array $data)
    {
        if (!isset($this->clients[$sessionId])) return;
        
        try {
            $connection = $this->clients[$sessionId]['connection'];
            $connection->write(json_encode($data));
        } catch (\Exception $e) {
            echo "❌ Failed to send to client: {$e->getMessage()}\n";
        }
    }
    
    private function broadcastToAll(array $data)
    {
        foreach ($this->clients as $sessionId => $client) {
            $this->sendToClient($sessionId, $data);
        }
    }
    
    private function broadcastToOthers($senderSessionId, array $data)
    {
        foreach ($this->clients as $sessionId => $client) {
            if ($sessionId !== $senderSessionId) {
                $this->sendToClient($sessionId, $data);
            }
        }
    }
    
    private function getClientIP($connection): string
    {
        // For ReactPHP, we'll use a simple approach
        return $connection->getRemoteAddress() ?? '127.0.0.1';
    }
    
    // Helper method to get country info from IP
    private function getCountryFromIP(string $ip): ?array
    {
        try {
            // Using ip-api.com (free, no key required)
            $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'user_agent' => 'PvP Calculator WebSocket Server'
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
            error_log('WebSocket Geolocation error: ' . $e->getMessage());
        }
        
        return [
            'code' => 'UNKNOWN',
            'name' => 'Unknown',
            'flag' => '🌍'
        ];
    }
    
    private function getCountryFlag(string $countryCode): string
    {
        $countryCode = strtoupper($countryCode);
        
        if (strlen($countryCode) === 2) {
            $flag = '';
            for ($i = 0; $i < 2; $i++) {
                $flag .= mb_chr(ord($countryCode[$i]) - ord('A') + 0x1F1E6, 'UTF-8');
            }
            return $flag;
        }
        
        return '🌍';
    }
    
    // Helper method to record page views
    private function recordPageView(string $page): void
    {
        // Simple page view tracking - you can expand this
        $logFile = __DIR__ . '/../../storage/page_views.json';
        
        $views = [];
        if (file_exists($logFile)) {
            $views = json_decode(file_get_contents($logFile), true) ?: [];
        }
        
        $today = date('Y-m-d');
        if (!isset($views[$today])) {
            $views[$today] = [];
        }
        
        if (!isset($views[$today][$page])) {
            $views[$today][$page] = 0;
        }
        
        $views[$today][$page]++;
        
        file_put_contents($logFile, json_encode($views, JSON_PRETTY_PRINT));
    }
}