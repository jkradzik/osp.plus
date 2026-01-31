import { BrowserRouter, Routes, Route, Navigate, Link } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { Layout } from './components/Layout';
import { LoginForm } from './components/LoginForm';
import { MemberList } from './components/MemberList';
import { MemberForm } from './components/MemberForm';
import { FeeList } from './components/FeeList';
import { DecorationList } from './components/DecorationList';
import { EquipmentList } from './components/EquipmentList';
import { FinancialList } from './components/FinancialList';
import './App.css';

function ProtectedRoute({ children }) {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? children : <Navigate to="/login" replace />;
}

function PublicRoute({ children }) {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? <Navigate to="/" replace /> : children;
}

function FinanceRoute({ children }) {
  const { canAccessFinances } = useAuth();
  return canAccessFinances() ? children : <Navigate to="/" replace />;
}

function Dashboard() {
  const { getRoleName, canAccessFinances } = useAuth();
  return (
    <div className="dashboard">
      <h2>Panel główny</h2>
      <p>Witaj w systemie OSP.plus! Jesteś zalogowany jako {getRoleName()}.</p>
      <div className="dashboard-links">
        <Link to="/members" className="dashboard-card">
          <h3>Członkowie</h3>
          <p>Zarządzaj ewidencją członków</p>
        </Link>
        <Link to="/fees" className="dashboard-card">
          <h3>Składki</h3>
          <p>Przeglądaj i waliduj składki</p>
        </Link>
        <Link to="/decorations" className="dashboard-card">
          <h3>Odznaczenia</h3>
          <p>Ewidencja odznaczeń członków</p>
        </Link>
        <Link to="/equipment" className="dashboard-card">
          <h3>Wyposażenie</h3>
          <p>Wyposażenie osobiste członków</p>
        </Link>
        {canAccessFinances() && (
          <Link to="/finances" className="dashboard-card">
            <h3>Finanse</h3>
            <p>Ewidencja przychodów i kosztów</p>
          </Link>
        )}
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
        <Route path="finances" element={<FinanceRoute><FinancialList /></FinanceRoute>} />
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
