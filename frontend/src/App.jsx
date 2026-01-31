import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { Layout } from './components/Layout';
import { LoginForm } from './components/LoginForm';
import { MemberList } from './components/MemberList';
import { MemberForm } from './components/MemberForm';
import { FeeList } from './components/FeeList';
import { DecorationList } from './components/DecorationList';
import { EquipmentList } from './components/EquipmentList';
import './App.css';

function ProtectedRoute({ children }) {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? children : <Navigate to="/login" replace />;
}

function PublicRoute({ children }) {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? <Navigate to="/" replace /> : children;
}

function Dashboard() {
  return (
    <div className="dashboard">
      <h2>Panel główny</h2>
      <p>Witaj w systemie OSP.plus!</p>
      <div className="dashboard-links">
        <a href="/members" className="dashboard-card">
          <h3>Członkowie</h3>
          <p>Zarządzaj ewidencją członków</p>
        </a>
        <a href="/fees" className="dashboard-card">
          <h3>Składki</h3>
          <p>Przeglądaj i waliduj składki</p>
        </a>
        <a href="/decorations" className="dashboard-card">
          <h3>Odznaczenia</h3>
          <p>Ewidencja odznaczeń członków</p>
        </a>
        <a href="/equipment" className="dashboard-card">
          <h3>Wyposażenie</h3>
          <p>Wyposażenie osobiste członków</p>
        </a>
      </div>
    </div>
  );
}

function AppRoutes() {
  return (
    <Routes>
      <Route
        path="/login"
        element={
          <PublicRoute>
            <LoginForm />
          </PublicRoute>
        }
      />
      <Route
        path="/"
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route index element={<Dashboard />} />
        <Route path="members" element={<MemberList />} />
        <Route path="members/new" element={<MemberForm />} />
        <Route path="members/:id/edit" element={<MemberForm />} />
        <Route path="fees" element={<FeeList />} />
        <Route path="decorations" element={<DecorationList />} />
        <Route path="equipment" element={<EquipmentList />} />
      </Route>
    </Routes>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </BrowserRouter>
  );
}
