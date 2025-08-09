import React, { useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { useAppDispatch, useAppSelector } from '../store';
import { checkAuthState } from '../store/authSlice';
import { 
  LoginScreen,
  HomeScreen,
  NotificationsScreen,
  ProfileScreen,
  InvoicesScreen,
  MaintenanceScreen
} from '../screens';
import { ApiTestScreen } from '../screens/ApiTestScreen';
import { LoadingSpinner } from '../components';

const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

const TabNavigator = () => {
  const notifications = useAppSelector((state) => state.notifications);
  const unreadCount = notifications?.unreadCount || 0;

  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        tabBarIcon: ({ focused, color, size }) => {
          let iconName: keyof typeof Ionicons.glyphMap;

          switch (route.name) {
            case 'Home':
              iconName = focused ? 'home' : 'home-outline';
              break;
            case 'Notifications':
              iconName = focused ? 'notifications' : 'notifications-outline';
              break;
            case 'Invoices':
              iconName = focused ? 'receipt' : 'receipt-outline';
              break;
            case 'Maintenance':
              iconName = focused ? 'construct' : 'construct-outline';
              break;
            case 'Profile':
              iconName = focused ? 'person' : 'person-outline';
              break;
            default:
              iconName = 'help-outline';
          }

          return <Ionicons name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: '#007AFF',
        tabBarInactiveTintColor: '#8E8E93',
        tabBarStyle: {
          backgroundColor: '#FFFFFF',
          borderTopWidth: 1,
          borderTopColor: '#E1E1E6',
          paddingBottom: 5,
          paddingTop: 5,
          height: 60,
        },
        headerStyle: {
          backgroundColor: '#FFFFFF',
          borderBottomWidth: 1,
          borderBottomColor: '#E1E1E6',
        },
        headerTitleStyle: {
          fontSize: 18,
          fontWeight: '600',
          color: '#333',
        },
      })}
    >
      <Tab.Screen 
        name="Home" 
        component={HomeScreen} 
        options={{ 
          title: 'Trang chủ',
          headerShown: false,
        }} 
      />
      <Tab.Screen 
        name="Notifications" 
        component={NotificationsScreen} 
        options={{ 
          title: 'Thông báo',
          tabBarBadge: unreadCount > 0 ? (unreadCount > 99 ? '99+' : String(unreadCount)) : undefined,
        }} 
      />
      <Tab.Screen 
        name="Invoices" 
        component={InvoicesScreen} 
        options={{ title: 'Hóa đơn' }} 
      />
      <Tab.Screen 
        name="Maintenance" 
        component={MaintenanceScreen} 
        options={{ title: 'Bảo trì' }} 
      />
      <Tab.Screen 
        name="Profile" 
        component={ProfileScreen} 
        options={{ title: 'Hồ sơ' }} 
      />
      <Tab.Screen 
        name="ApiTest" 
        component={ApiTestScreen} 
        options={{ title: 'API Test' }} 
      />
    </Tab.Navigator>
  );
};

export const AppNavigator = () => {
  const dispatch = useAppDispatch();
  const { isAuthenticated, isLoading, isInitialized } = useAppSelector(state => state.auth);

  useEffect(() => {
    dispatch(checkAuthState());
  }, [dispatch]);

  if (!isInitialized || isLoading) {
    return <LoadingSpinner />;
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        {isAuthenticated ? (
          <Stack.Screen name="Main" component={TabNavigator} />
        ) : (
          <Stack.Screen name="Login" component={LoginScreen} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
};
