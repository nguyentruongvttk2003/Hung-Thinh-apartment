import React, { useEffect } from 'react';
import { StatusBar } from 'expo-status-bar';
import { Provider } from 'react-redux';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useDispatch } from 'react-redux';
import { store, AppDispatch } from './src/store';
import { loadStoredAuth } from './src/store/authSlice';
import { AppNavigator } from './src/navigation/AppNavigator';
import { NetworkDebugger } from './src/components/NetworkDebugger';

function AppContent() {
  const dispatch = useDispatch<AppDispatch>();

  useEffect(() => {
    // Load stored authentication data on app start
    dispatch(loadStoredAuth());
  }, [dispatch]);

  return (
    <>
      <StatusBar style="auto" />
      <AppNavigator />
      {__DEV__ && <NetworkDebugger />}
    </>
  );
}

export default function App() {
  return (
    <Provider store={store}>
      <SafeAreaProvider>
        <AppContent />
      </SafeAreaProvider>
    </Provider>
  );
}
