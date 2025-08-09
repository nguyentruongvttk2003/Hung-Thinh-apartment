import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  SafeAreaView,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Image
} from 'react-native';
import { useAppDispatch, useAppSelector } from '../../store';
import { login, clearError } from '../../store/authSlice';
import { Input, Button, LoadingSpinner } from '../../components';
import { validation, alertUtils } from '../../utils';

export const LoginScreen = () => {
  const dispatch = useAppDispatch();
  const { isLoading, error, isAuthenticated } = useAppSelector(state => state.auth);
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  
  const [formErrors, setFormErrors] = useState({
    email: '',
    password: '',
  });

  useEffect(() => {
    if (error) {
      alertUtils.error(error);
      dispatch(clearError());
    }
  }, [error, dispatch]);

  const validateForm = (): boolean => {
    const errors = {
      email: '',
      password: '',
    };

    if (validation.isEmpty(formData.email)) {
      errors.email = 'Vui lòng nhập email';
    } else if (!validation.isEmail(formData.email)) {
      errors.email = 'Email không hợp lệ';
    }

    if (validation.isEmpty(formData.password)) {
      errors.password = 'Vui lòng nhập mật khẩu';
    } else if (!validation.isPassword(formData.password)) {
      errors.password = 'Mật khẩu phải có ít nhất 6 ký tự';
    }

    setFormErrors(errors);
    return !errors.email && !errors.password;
  };

  const handleLogin = async () => {
    if (!validateForm()) {
      return;
    }

    try {
      await dispatch(login(formData)).unwrap();
      // Navigation will be handled by AppNavigator based on isAuthenticated state
    } catch (error) {
      // Error is handled by useEffect
    }
  };

  const handleInputChange = (field: keyof typeof formData) => (value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    
    // Clear error when user starts typing
    if (formErrors[field]) {
      setFormErrors(prev => ({ ...prev, [field]: '' }));
    }
  };

  if (isLoading) {
    return <LoadingSpinner />;
  }

  return (
    <SafeAreaView style={styles.container}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboardView}
      >
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
        >
          <View style={styles.logoContainer}>
            <Image
              source={{ uri: 'https://via.placeholder.com/120x120/007AFF/FFFFFF?text=LOGO' }}
              style={styles.logo}
              resizeMode="contain"
            />
            <Text style={styles.title}>Quản lý chung cư</Text>
            <Text style={styles.subtitle}>Đăng nhập để tiếp tục</Text>
          </View>

          <View style={styles.formContainer}>
            <Input
              label="Email"
              value={formData.email}
              onChangeText={handleInputChange('email')}
              error={formErrors.email}
              placeholder="Nhập email của bạn"
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
              required
            />

            <Input
              label="Mật khẩu"
              value={formData.password}
              onChangeText={handleInputChange('password')}
              error={formErrors.password}
              placeholder="Nhập mật khẩu"
              secureTextEntry
              required
            />

            <Button
              title="Đăng nhập"
              onPress={handleLogin}
              loading={isLoading}
              disabled={isLoading}
              style={styles.loginButton}
            />
          </View>

          <View style={styles.footer}>
            <Text style={styles.footerText}>
              Quên mật khẩu? Liên hệ ban quản lý
            </Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F9FA',
  },
  keyboardView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    padding: 24,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 48,
  },
  logo: {
    width: 120,
    height: 120,
    marginBottom: 24,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
  },
  formContainer: {
    marginBottom: 32,
  },
  loginButton: {
    marginTop: 16,
  },
  footer: {
    alignItems: 'center',
  },
  footerText: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
  },
});
