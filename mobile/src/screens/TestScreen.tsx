import React from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  SafeAreaView,
  TouchableOpacity,
} from 'react-native';

export const TestScreen = () => {
  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.content}>
        <Text style={styles.title}>Test Screen</Text>
        
        <TouchableOpacity style={styles.button}>
          <Text style={styles.buttonText}>Test Button</Text>
        </TouchableOpacity>
        
        <View style={styles.textContainer}>
          <Text style={styles.text}>Regular text</Text>
          <Text style={styles.text}>{String(123)}</Text>
          <Text style={styles.text}>{String(true)}</Text>
          <Text style={styles.text}>{String(null) || 'null fallback'}</Text>
          <Text style={styles.text}>{String(undefined) || 'undefined fallback'}</Text>
        </View>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F9FA',
  },
  content: {
    flex: 1,
    padding: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 20,
    color: '#333',
  },
  button: {
    backgroundColor: '#007AFF',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    marginBottom: 20,
  },
  buttonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  textContainer: {
    alignItems: 'center',
  },
  text: {
    fontSize: 16,
    color: '#333',
    marginBottom: 8,
  },
});
