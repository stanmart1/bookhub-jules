import { useState, useEffect } from 'react';
import { apiClient, User, setAuthToken, removeAuthToken, getAuthToken } from '../lib/api';

export const useAuth = () => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const initAuth = async () => {
      const token = getAuthToken();
      if (token) {
        try {
          const userData = await apiClient.getCurrentUser();
          setUser(userData);
        } catch (err) {
          removeAuthToken();
          setError(err instanceof Error ? err.message : 'Authentication failed');
        }
      }
      setLoading(false);
    };

    initAuth();
  }, []);

  const login = async (email: string, password: string) => {
    try {
      setLoading(true);
      setError(null);
      
      const { user: userData, token } = await apiClient.login({ email, password });
      setAuthToken(token);
      setUser(userData);
      return { success: true };
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Login failed';
      setError(errorMessage);
      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  };

  const register = async (name: string, email: string, password: string, password_confirmation: string) => {
    try {
      setLoading(true);
      setError(null);
      
      const { user: userData, token } = await apiClient.register({ 
        name, 
        email, 
        password, 
        password_confirmation 
      });
      setAuthToken(token);
      setUser(userData);
      return { success: true };
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Registration failed';
      setError(errorMessage);
      return { success: false, error: errorMessage };
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      await apiClient.logout();
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      removeAuthToken();
      setUser(null);
      setError(null);
    }
  };

  return {
    user,
    loading,
    error,
    login,
    register,
    logout,
    isAuthenticated: !!user,
  };
}; 