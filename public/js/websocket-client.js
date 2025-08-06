class WebSocketClient {
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.heartbeatInterval = null;
        this.isConnected = false;
        
        this.init();
    }
    
    init() {
        this.connect();
    }
    
    connect() {
        try {
            // For Railway deployment, use the same port as HTTP
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const host = window.location.hostname;
            
            // Railway uses the same port for both HTTP and WebSocket
            let wsUrl;
            if (host.includes('railway.app')) {
                // Production Railway - use same port
                wsUrl = `${protocol}//${host}`;
            } else {
                // Local development
                const port = window.location.port ? `:${window.location.port}` : ':8080';
                wsUrl = `${protocol}//${host}${port}`;
            }
            
            console.log('🔌 Connecting to WebSocket:', wsUrl);
            
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = (event) => {
                console.log('✅ WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.startHeartbeat();
                this.updateConnectionStatus('connected');
            };
            
            this.ws.onmessage = (event) => {
                this.handleMessage(JSON.parse(event.data));
            };
            
            this.ws.onclose = (event) => {
                console.log('❌ WebSocket disconnected:', event.code, event.reason);
                this.isConnected = false;
                this.stopHeartbeat();
                this.updateConnectionStatus('disconnected');
                this.scheduleReconnect();
            };
            
            this.ws.onerror = (error) => {
                console.error('🚨 WebSocket error:', error);
                this.updateConnectionStatus('error');
            };
            
        } catch (error) {
            console.error('🚨 Failed to create WebSocket:', error);
            this.scheduleReconnect();
        }
    }
    
    handleMessage(data) {
        console.log('📨 Received:', data);
        
        switch (data.type) {
            case 'connection_established':
                this.handleConnectionEstablished(data);
                break;
                
            case 'visitor_update':
                this.handleVisitorUpdate(data);
                break;
                
            case 'heartbeat_ack':
                this.handleHeartbeatAck(data);
                break;
                
            case 'user_activity_update':
                this.handleUserActivity(data);
                break;
                
            case 'calculator_usage_update':
                this.handleCalculatorUsage(data);
                break;
        }
    }
    
    handleConnectionEstablished(data) {
        console.log('🎉 Connection established:', data.sessionId);
        this.sessionId = data.sessionId;
        
        // Send initial page view
        this.sendMessage({
            type: 'page_view',
            page: window.location.pathname
        });
    }
    
    handleVisitorUpdate(data) {
        this.updateVisitorStats(data.visitor_stats);
        this.updateOnlineCountries(data.online_countries);
    }
    
    handleHeartbeatAck(data) {
        console.log('💓 Heartbeat acknowledged, latency:', data.latency + 'ms');
    }
    
    handleUserActivity(data) {
        console.log('👤 User activity:', data.data);
        // You can add visual indicators for user activity here
    }
    
    handleCalculatorUsage(data) {
        console.log('🧮 Calculator usage:', data.data);
        this.showCalculatorUsageNotification(data.data);
    }
    
    sendMessage(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
            console.warn('⚠️ WebSocket not connected, message not sent:', data);
        }
    }
    
    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            this.sendMessage({
                type: 'heartbeat',
                timestamp: Math.floor(Date.now() / 1000)
            });
        }, 30000); // Every 30 seconds
    }
    
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }
    
    scheduleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
            
            console.log(`🔄 Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.connect();
            }, delay);
        } else {
            console.error('❌ Max reconnection attempts reached');
            this.updateConnectionStatus('failed');
        }
    }
    
    updateConnectionStatus(status) {
        const statusElement = document.getElementById('ws-status');
        if (statusElement) {
            statusElement.className = `ws-status ws-status-${status}`;
            statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }
    }
    
    updateVisitorStats(stats) {
        const totalVisitors = Object.values(stats).reduce((sum, country) => sum + country.count, 0);
        
        const totalElement = document.getElementById('total-visitors');
        if (totalElement) {
            totalElement.textContent = totalVisitors.toLocaleString();
        }
        
        const countriesElement = document.getElementById('visitor-countries');
        if (countriesElement) {
            countriesElement.innerHTML = '';
            
            Object.entries(stats).slice(0, 5).forEach(([code, country]) => {
                const countryDiv = document.createElement('div');
                countryDiv.className = 'country-stat';
                countryDiv.innerHTML = `
                    <span class="flag">${country.flag}</span>
                    <span class="name">${country.name}</span>
                    <span class="count">${country.count}</span>
                `;
                countriesElement.appendChild(countryDiv);
            });
        }
    }
    
    updateOnlineCountries(countries) {
        const totalOnline = Object.values(countries).reduce((sum, country) => sum + country.online_count, 0);
        
        const onlineElement = document.getElementById('online-visitors');
        if (onlineElement) {
            onlineElement.textContent = totalOnline;
        }
        
        const onlineCountriesElement = document.getElementById('online-countries');
        if (onlineCountriesElement) {
            onlineCountriesElement.innerHTML = '';
            
            Object.entries(countries).forEach(([code, country]) => {
                const countryDiv = document.createElement('div');
                countryDiv.className = 'online-country';
                countryDiv.innerHTML = `
                    <span class="flag">${country.flag}</span>
                    <span class="name">${country.name}</span>
                    <span class="online-count">${country.online_count}</span>
                `;
                onlineCountriesElement.appendChild(countryDiv);
            });
        }
    }
    
    showCalculatorUsageNotification(data) {
        const notification = document.createElement('div');
        notification.className = 'calculator-notification';
        notification.innerHTML = `
            <span class="flag">${data.country.flag}</span>
            <span>Someone from ${data.country.name} used the calculator!</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    // Public methods for interaction
    reportCalculatorUsage(calculationType) {
        this.sendMessage({
            type: 'calculator_usage',
            calculation_type: calculationType
        });
    }
    
    reportUserActivity(activity) {
        this.sendMessage({
            type: 'user_activity',
            activity: activity
        });
    }
}

// Initialize WebSocket client when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.wsClient = new WebSocketClient();
});