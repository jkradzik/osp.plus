import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const { login, isLoading } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    try {
      await login(email, password);
      navigate('/');
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <div className="login-container">
      <h1>OSP.plus</h1>
      <h2>Logowanie</h2>

      <form onSubmit={handleSubmit} className="login-form">
        {error && <div className="error-message">{error}</div>}

        <div className="form-group">
          <label htmlFor="email">Email</label>
          <input
            type="email"
            id="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            autoComplete="email"
          />
        </div>

        <div className="form-group">
          <label htmlFor="password">Hasło</label>
          <input
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            autoComplete="current-password"
          />
        </div>

        <button type="submit" disabled={isLoading}>
          {isLoading ? 'Logowanie...' : 'Zaloguj'}
        </button>
      </form>

      <div className="demo-credentials">
        <p>Szybkie logowanie demo:</p>
        <div className="demo-buttons">
          <button type="button" onClick={() => { setEmail('admin@osp.plus'); setPassword('admin123'); }} className="demo-btn admin">
            Administrator
          </button>
          <button type="button" onClick={() => { setEmail('prezes@osp.plus'); setPassword('prezes123'); }} className="demo-btn prezes">
            Prezes
          </button>
          <button type="button" onClick={() => { setEmail('skarbnik@osp.plus'); setPassword('skarbnik123'); }} className="demo-btn skarbnik">
            Skarbnik
          </button>
          <button type="button" onClick={() => { setEmail('naczelnik@osp.plus'); setPassword('naczelnik123'); }} className="demo-btn naczelnik">
            Naczelnik
          </button>
          <button type="button" onClick={() => { setEmail('user@osp.plus'); setPassword('user123'); }} className="demo-btn user">
            Druh
          </button>
        </div>
      </div>
    </div>
  );
}
