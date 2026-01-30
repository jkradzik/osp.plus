import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function Layout() {
  const { logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="app-layout">
      <header className="app-header">
        <div className="header-brand">
          <Link to="/">OSP.plus</Link>
        </div>
        <nav className="header-nav">
          <Link to="/members">Członkowie</Link>
          <Link to="/fees">Składki</Link>
        </nav>
        <button onClick={handleLogout} className="btn btn-small">
          Wyloguj
        </button>
      </header>

      <main className="app-main">
        <Outlet />
      </main>

      <footer className="app-footer">
        <p>OSP.plus - System zarządzania jednostką OSP</p>
      </footer>
    </div>
  );
}
