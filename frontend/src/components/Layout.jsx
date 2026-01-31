import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Button } from './ui/button';

export function Layout() {
  const { logout, user, getRoleName, canAccessFinances } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen flex flex-col">
      <header className="bg-primary text-primary-foreground px-8 py-4 flex items-center gap-8">
        <div className="font-bold text-xl">
          <Link to="/" className="text-primary-foreground no-underline hover:opacity-90">
            OSP.plus
          </Link>
        </div>
        <nav className="flex gap-6 flex-1">
          <Link to="/members" className="text-primary-foreground/90 no-underline hover:text-primary-foreground">
            Członkowie
          </Link>
          <Link to="/fees" className="text-primary-foreground/90 no-underline hover:text-primary-foreground">
            Składki
          </Link>
          <Link to="/decorations" className="text-primary-foreground/90 no-underline hover:text-primary-foreground">
            Odznaczenia
          </Link>
          <Link to="/equipment" className="text-primary-foreground/90 no-underline hover:text-primary-foreground">
            Wyposażenie
          </Link>
          {canAccessFinances() && (
            <Link to="/finances" className="text-primary-foreground/90 no-underline hover:text-primary-foreground">
              Finanse
            </Link>
          )}
        </nav>
        <div className="flex items-center gap-4 ml-auto">
          <span className="text-sm opacity-90">
            {user?.email} ({getRoleName()})
          </span>
          <Button onClick={handleLogout} variant="secondary" size="sm">
            Wyloguj
          </Button>
        </div>
      </header>

      <main className="flex-1 p-8 w-full">
        <Outlet />
      </main>

      <footer className="bg-foreground/90 text-muted py-4 text-center text-sm">
        <p>OSP.plus - System zarządzania jednostką OSP</p>
      </footer>
    </div>
  );
}