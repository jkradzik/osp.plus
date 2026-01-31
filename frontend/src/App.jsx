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
import { Card, CardHeader, CardTitle, CardDescription } from './components/ui/card';

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
    <div className="text-center">
      <h2 className="text-2xl font-bold mb-2">Panel główny</h2>
      <p className="text-muted-foreground mb-8">
        Witaj w systemie OSP.plus! Jesteś zalogowany jako {getRoleName()}.
      </p>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <Link to="/members" className="no-underline">
          <Card className="h-full transition-all hover:-translate-y-1 hover:shadow-lg cursor-pointer">
            <CardHeader>
              <CardTitle className="text-primary">Członkowie</CardTitle>
              <CardDescription>Zarządzaj ewidencją członków</CardDescription>
            </CardHeader>
          </Card>
        </Link>
        <Link to="/fees" className="no-underline">
          <Card className="h-full transition-all hover:-translate-y-1 hover:shadow-lg cursor-pointer">
            <CardHeader>
              <CardTitle className="text-primary">Składki</CardTitle>
              <CardDescription>Przeglądaj i waliduj składki</CardDescription>
            </CardHeader>
          </Card>
        </Link>
        <Link to="/decorations" className="no-underline">
          <Card className="h-full transition-all hover:-translate-y-1 hover:shadow-lg cursor-pointer">
            <CardHeader>
              <CardTitle className="text-primary">Odznaczenia</CardTitle>
              <CardDescription>Ewidencja odznaczeń członków</CardDescription>
            </CardHeader>
          </Card>
        </Link>
        <Link to="/equipment" className="no-underline">
          <Card className="h-full transition-all hover:-translate-y-1 hover:shadow-lg cursor-pointer">
            <CardHeader>
              <CardTitle className="text-primary">Wyposażenie</CardTitle>
              <CardDescription>Wyposażenie osobiste członków</CardDescription>
            </CardHeader>
          </Card>
        </Link>
        {canAccessFinances() && (
          <Link to="/finances" className="no-underline">
            <Card className="h-full transition-all hover:-translate-y-1 hover:shadow-lg cursor-pointer">
              <CardHeader>
                <CardTitle className="text-primary">Finanse</CardTitle>
                <CardDescription>Ewidencja przychodów i kosztów</CardDescription>
              </CardHeader>
            </Card>
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
