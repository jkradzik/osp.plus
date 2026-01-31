import { createContext, useContext, useState } from 'react';
import { api } from '../services/api';

const AuthContext = createContext(null);

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}

export function AuthProvider({ children }) {
  const [isAuthenticated, setIsAuthenticated] = useState(api.isAuthenticated());
  const [user, setUser] = useState(api.getUserInfo());
  const [isLoading, setIsLoading] = useState(false);

  const login = async (email, password) => {
    setIsLoading(true);
    try {
      await api.login(email, password);
      setIsAuthenticated(true);
      setUser(api.getUserInfo());
      return true;
    } finally {
      setIsLoading(false);
    }
  };

  const logout = () => {
    api.logout();
    setIsAuthenticated(false);
    setUser(null);
  };

  const hasRole = (role) => {
    return user?.roles?.includes(role) || false;
  };

  const canAccessFinances = () => {
    return user?.roles?.some(r => ['ROLE_ADMIN', 'ROLE_PREZES', 'ROLE_SKARBNIK', 'ROLE_NACZELNIK'].includes(r)) || false;
  };

  const getRoleName = () => {
    if (!user?.roles) return '';
    if (user.roles.includes('ROLE_ADMIN')) return 'Administrator';
    if (user.roles.includes('ROLE_PREZES')) return 'Prezes';
    if (user.roles.includes('ROLE_SKARBNIK')) return 'Skarbnik';
    if (user.roles.includes('ROLE_NACZELNIK')) return 'Naczelnik';
    return 'Druh';
  };

  return (
    <AuthContext.Provider value={{ isAuthenticated, isLoading, user, login, logout, hasRole, canAccessFinances, getRoleName }}>
      {children}
    </AuthContext.Provider>
  );
}
