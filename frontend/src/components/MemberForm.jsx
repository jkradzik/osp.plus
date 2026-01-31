import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../services/api';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { Button } from './ui/button';
import { Alert, AlertDescription } from './ui/alert';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './ui/select';

const MEMBERSHIP_STATUSES = [
  { value: 'active', label: 'Aktywny' },
  { value: 'inactive', label: 'Nieaktywny' },
  { value: 'honorary', label: 'Honorowy' },
  { value: 'supporting', label: 'Wspierający' },
  { value: 'youth', label: 'MDP' },
  { value: 'removed', label: 'Usunięty' },
  { value: 'deceased', label: 'Zmarły' },
];

const emptyMember = {
  firstName: '',
  lastName: '',
  pesel: '',
  email: '',
  phone: '',
  address: '',
  birthDate: '',
  joinDate: '',
  membershipStatus: 'active',
  boardPosition: '',
};

export function MemberForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;

  const [member, setMember] = useState(emptyMember);
  const [loading, setLoading] = useState(isEdit);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (id) {
      loadMember();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const loadMember = async () => {
    try {
      const data = await api.getMember(id);
      setMember({
        firstName: data.firstName || '',
        lastName: data.lastName || '',
        pesel: data.pesel || '',
        email: data.email || '',
        phone: data.phone || '',
        address: data.address || '',
        birthDate: data.birthDate?.split('T')[0] || '',
        joinDate: data.joinDate?.split('T')[0] || '',
        membershipStatus: data.membershipStatus || 'active',
        boardPosition: data.boardPosition || '',
      });
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setMember((prev) => ({ ...prev, [name]: value }));
  };

  const handleStatusChange = (value) => {
    setMember((prev) => ({ ...prev, membershipStatus: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSaving(true);

    try {
      const payload = {
        ...member,
        email: member.email || null,
        phone: member.phone || null,
        address: member.address || null,
        boardPosition: member.boardPosition || null,
      };

      if (isEdit) {
        await api.updateMember(id, payload);
      } else {
        await api.createMember(payload);
      }
      navigate('/members');
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="text-center py-8 text-muted-foreground">Ładowanie...</div>;
  }

  return (
    <Card className="max-w-2xl mx-auto">
      <CardHeader>
        <CardTitle>{isEdit ? 'Edycja członka' : 'Nowy członek'}</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-6">
          {error && (
            <Alert variant="destructive">
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="firstName">Imię *</Label>
              <Input
                type="text"
                id="firstName"
                name="firstName"
                value={member.firstName}
                onChange={handleChange}
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="lastName">Nazwisko *</Label>
              <Input
                type="text"
                id="lastName"
                name="lastName"
                value={member.lastName}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="pesel">PESEL *</Label>
              <Input
                type="text"
                id="pesel"
                name="pesel"
                value={member.pesel}
                onChange={handleChange}
                pattern="\d{11}"
                maxLength={11}
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="membershipStatus">Status *</Label>
              <Select value={member.membershipStatus} onValueChange={handleStatusChange}>
                <SelectTrigger>
                  <SelectValue placeholder="Wybierz status" />
                </SelectTrigger>
                <SelectContent>
                  {MEMBERSHIP_STATUSES.map((s) => (
                    <SelectItem key={s.value} value={s.value}>
                      {s.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="birthDate">Data urodzenia *</Label>
              <Input
                type="date"
                id="birthDate"
                name="birthDate"
                value={member.birthDate}
                onChange={handleChange}
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="joinDate">Data wstąpienia *</Label>
              <Input
                type="date"
                id="joinDate"
                name="joinDate"
                value={member.joinDate}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                type="email"
                id="email"
                name="email"
                value={member.email}
                onChange={handleChange}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="phone">Telefon</Label>
              <Input
                type="tel"
                id="phone"
                name="phone"
                value={member.phone}
                onChange={handleChange}
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="address">Adres</Label>
            <Textarea
              id="address"
              name="address"
              value={member.address}
              onChange={handleChange}
              rows={2}
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="boardPosition">Funkcja w zarządzie</Label>
            <Input
              type="text"
              id="boardPosition"
              name="boardPosition"
              value={member.boardPosition}
              onChange={handleChange}
            />
          </div>

          <div className="flex justify-end gap-4 pt-4">
            <Button type="button" variant="outline" onClick={() => navigate('/members')}>
              Anuluj
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? 'Zapisywanie...' : 'Zapisz'}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
