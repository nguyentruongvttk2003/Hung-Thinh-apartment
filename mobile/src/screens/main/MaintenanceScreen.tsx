import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

export const MaintenanceScreen = () => {
  return (
    <View style={styles.container}>
      <Text style={styles.title}>Bảo trì</Text>
      <Text style={styles.subtitle}>Chức năng đang được phát triển</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#F8F9FA',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 10,
    color: '#333',
  },
  subtitle: {
    fontSize: 16,
    textAlign: 'center',
    color: '#666',
  },
});
