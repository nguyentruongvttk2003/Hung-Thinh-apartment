import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, Alert } from 'react-native';
import apiService from '../services/apiService';
import dashboardService from '../services/dashboardService';
import notificationService from '../services/notificationService';
import eventService from '../services/eventService';
import feedbackService from '../services/feedbackService';

export const ApiTestScreen = () => {
  const [testResults, setTestResults] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(false);

  const addResult = (message: string) => {
    setTestResults(prev => [...prev, `${new Date().toLocaleTimeString()}: ${message}`]);
  };

  const clearResults = () => {
    setTestResults([]);
  };

  const testHealthCheck = async () => {
    try {
      addResult('Testing health check...');
      const response = await apiService.get<any>('/health');
      addResult(`✅ Health check successful: ${response.message}`);
    } catch (error) {
      addResult(`❌ Health check failed: ${error}`);
    }
  };

  const testDashboardActivities = async () => {
    try {
      addResult('Testing dashboard activities...');
      const activities = await dashboardService.getRecentActivities();
      addResult(`✅ Dashboard activities: ${activities.length} items`);
    } catch (error) {
      addResult(`❌ Dashboard activities failed: ${error}`);
    }
  };

  const testNotifications = async () => {
    try {
      addResult('Testing notifications...');
      const notifications = await notificationService.getNotifications(1);
      addResult(`✅ Notifications: ${notifications.data?.length || 0} items`);
    } catch (error) {
      addResult(`❌ Notifications failed: ${error}`);
    }
  };

  const testEvents = async () => {
    try {
      addResult('Testing events...');
      const events = await eventService.getEvents(1);
      addResult(`✅ Events: ${events.data?.length || 0} items`);
    } catch (error) {
      addResult(`❌ Events failed: ${error}`);
    }
  };

  const testFeedbacks = async () => {
    try {
      addResult('Testing feedbacks...');
      const feedbacks = await feedbackService.getFeedbacks(1);
      addResult(`✅ Feedbacks: ${feedbacks.data?.length || 0} items`);
    } catch (error) {
      addResult(`❌ Feedbacks failed: ${error}`);
    }
  };

  const runAllTests = async () => {
    setIsLoading(true);
    clearResults();
    
    await testHealthCheck();
    await testDashboardActivities();
    await testNotifications();
    await testEvents();
    await testFeedbacks();
    
    setIsLoading(false);
    addResult('🎉 All tests completed!');
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>API Connection Test</Text>
      
      <View style={styles.buttonContainer}>
        <TouchableOpacity 
          style={[styles.button, styles.primaryButton]} 
          onPress={runAllTests}
          disabled={isLoading}
        >
          <Text style={styles.buttonText}>
            {isLoading ? 'Testing...' : 'Run All Tests'}
          </Text>
        </TouchableOpacity>
        
        <TouchableOpacity 
          style={[styles.button, styles.secondaryButton]} 
          onPress={clearResults}
        >
          <Text style={styles.secondaryButtonText}>Clear Results</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.individualTests}>
        <TouchableOpacity style={styles.smallButton} onPress={testHealthCheck}>
          <Text style={styles.smallButtonText}>Health</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.smallButton} onPress={testDashboardActivities}>
          <Text style={styles.smallButtonText}>Dashboard</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.smallButton} onPress={testNotifications}>
          <Text style={styles.smallButtonText}>Notifications</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.smallButton} onPress={testEvents}>
          <Text style={styles.smallButtonText}>Events</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.smallButton} onPress={testFeedbacks}>
          <Text style={styles.smallButtonText}>Feedbacks</Text>
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.resultsContainer}>
        {testResults.map((result, index) => (
          <Text key={index} style={styles.resultText}>
            {result}
          </Text>
        ))}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#F8F9FA',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 20,
    color: '#333',
  },
  buttonContainer: {
    gap: 10,
    marginBottom: 20,
  },
  button: {
    padding: 15,
    borderRadius: 10,
    alignItems: 'center',
  },
  primaryButton: {
    backgroundColor: '#007AFF',
  },
  secondaryButton: {
    backgroundColor: '#FFF',
    borderWidth: 1,
    borderColor: '#007AFF',
  },
  buttonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  secondaryButtonText: {
    color: '#007AFF',
    fontSize: 16,
    fontWeight: '600',
  },
  individualTests: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 20,
  },
  smallButton: {
    backgroundColor: '#E5E5EA',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
  },
  smallButtonText: {
    color: '#333',
    fontSize: 12,
    fontWeight: '500',
  },
  resultsContainer: {
    flex: 1,
    backgroundColor: '#FFF',
    borderRadius: 10,
    padding: 15,
    maxHeight: 400,
  },
  resultText: {
    fontSize: 12,
    fontFamily: 'monospace',
    color: '#333',
    marginBottom: 5,
  },
});
