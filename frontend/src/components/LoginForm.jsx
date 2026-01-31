import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from './ui/card';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Button } from './ui/button';
import { Alert, AlertDescription } from './ui/alert';

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

  const setDemoCredentials = (demoEmail, demoPassword) => {
    setEmail(demoEmail);
    setPassword(demoPassword);
  };

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-background">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <CardTitle className="text-3xl text-primary">OSP.plus</CardTitle>
          <CardDescription className="text-lg">Logowanie</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && (
              <Alert variant="destructive">
                <AlertDescription>{error}</AlertDescription>
              </Alert>
            )}

            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                type="email"
                id="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                autoComplete="email"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">Hasło</Label>
              <Input
                type="password"
                id="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                autoComplete="current-password"
              />
            </div>

            <Button type="submit" disabled={isLoading} className="w-full">
              {isLoading ? 'Logowanie...' : 'Zaloguj'}
            </Button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-sm text-muted-foreground mb-3">Szybkie logowanie demo:</p>
            <div className="flex flex-wrap gap-2 justify-center">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setDemoCredentials('admin@osp.plus', 'admin123')}
                className="border-primary text-primary hover:bg-primary hover:text-primary-foreground"
              >
                Administrator
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setDemoCredentials('prezes@osp.plus', 'prezes123')}
                className="border-success text-success hover:bg-success hover:text-success-foreground"
              >
                Prezes
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setDemoCredentials('skarbnik@osp.plus', 'skarbnik123')}
                className="border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white"
              >
                Skarbnik
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setDemoCredentials('naczelnik@osp.plus', 'naczelnik123')}
                className="border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-white"
              >
                Naczelnik
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => setDemoCredentials('user@osp.plus', 'user123')}
                className="border-muted-foreground text-muted-foreground hover:bg-muted-foreground hover:text-white"
              >
                Druh
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
