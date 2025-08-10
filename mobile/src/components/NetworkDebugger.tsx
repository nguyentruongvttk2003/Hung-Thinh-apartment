import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { API_CONFIG } from '../config/api';

export const NetworkDebugger = () => {
  const [logs, setLogs] = useState<string[]>([]);
  const [isVisible, setIsVisible] = useState(false);

  const addLog = (message: string) => {
    const timestamp = new Date().toLocaleTimeString();
    setLogs(prev => [`[${timestamp}] ${message}`, ...prev.slice(0, 49)]);
  };

  const testConnection = async () => {
    addLog('🔍 Testing connection to backend...');
    addLog(`📍 Target URL: ${API_CONFIG.BASE_URL}/health`);

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => {
        controller.abort();
        addLog('⏰ Request timeout after 10 seconds');
      }, 10000);

      const response = await fetch(`${API_CONFIG.BASE_URL}/health`, {
        method: 'GET',
        signal: controller.signal,
        headers: {
          'Content-Type': 'application/json',
        },
      });

      clearTimeout(timeoutId);

      if (response.ok) {
        const data = await response.json();
        addLog(`✅ Success! Status: ${response.status}`);
        addLog(`📋 Response: ${JSON.stringify(data, null, 2)}`);
      } else {
        addLog(`❌ HTTP Error: ${response.status} ${response.statusText}`);
      }
    } catch (error: any) {
      addLog(`💥 Connection Error: ${error.message}`);
      addLog('🔧 Troubleshooting:');
      addLog('  1. Check if Laravel server is running');
      addLog('  2. Verify IP address is correct');
      addLog('  3. Check firewall/antivirus');
      addLog('  4. Ensure phone and PC on same WiFi');
    }
  };

  const testLocalhost = async () => {
    addLog('🏠 Testing localhost connection...');
    try {
      const response = await fetch('http://127.0.0.1:8000/api/health');
      addLog(`✅ Localhost works: ${response.status}`);
    } catch (error: any) {
      addLog(`❌ Localhost failed: ${error.message}`);
    }
  };

  useEffect(() => {
    addLog('🚀 Network Debugger initialized');
    addLog(`🌐 Base URL: ${API_CONFIG.BASE_URL}`);
  }, []);

  if (!isVisible) {
    return (
      <TouchableOpacity 
        style={styles.toggleButton}
        onPress={() => setIsVisible(true)}
      >
        <Text style={styles.toggleText}>🔧 Debug</Text>
      </TouchableOpacity>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Network Debugger</Text>
        <TouchableOpacity onPress={() => setIsVisible(false)}>
          <Text style={styles.closeButton}>✕</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.controls}>
        <TouchableOpacity style={styles.button} onPress={testConnection}>
          <Text style={styles.buttonText}>Test Connection</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.button} onPress={testLocalhost}>
          <Text style={styles.buttonText}>Test Localhost</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.button} onPress={() => setLogs([])}>
          <Text style={styles.buttonText}>Clear Logs</Text>
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.logContainer}>
        {logs.map((log, index) => (
          <Text key={index} style={styles.logText}>
            {log}
          </Text>
        ))}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  toggleButton: {
    position: 'absolute',
    top: 50,
    right: 20,
    backgroundColor: '#007AFF',
    padding: 8,
    borderRadius: 15,
    zIndex: 1000,
  },
  toggleText: {
    color: 'white',
    fontSize: 12,
    fontWeight: 'bold',
  },
  container: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.9)',
    zIndex: 999,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    paddingTop: 50,
    backgroundColor: '#1a1a1a',
  },
  title: {
    color: 'white',
    fontSize: 18,
    fontWeight: 'bold',
  },
  closeButton: {
    color: 'white',
    fontSize: 24,
    padding: 5,
  },
  controls: {
    flexDirection: 'row',
    padding: 10,
    gap: 10,
  },
  button: {
    flex: 1,
    backgroundColor: '#007AFF',
    padding: 10,
    borderRadius: 5,
    alignItems: 'center',
  },
  buttonText: {
    color: 'white',
    fontSize: 12,
    fontWeight: 'bold',
  },
  logContainer: {
    flex: 1,
    padding: 10,
  },
  logText: {
    color: '#00FF00',
    fontSize: 11,
    fontFamily: 'monospace',
    marginBottom: 2,
  },
});
