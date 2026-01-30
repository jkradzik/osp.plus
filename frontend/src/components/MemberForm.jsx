import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../services/api';

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

  if (loading) return <div className="loading">Ładowanie...</div>;

  return (
    <div className="member-form">
      <h2>{isEdit ? 'Edycja członka' : 'Nowy członek'}</h2>

      <form onSubmit={handleSubmit}>
        {error && <div className="error-message">{error}</div>}

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="firstName">Imię *</label>
            <input
              type="text"
              id="firstName"
              name="firstName"
              value={member.firstName}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="lastName">Nazwisko *</label>
            <input
              type="text"
              id="lastName"
              name="lastName"
              value={member.lastName}
              onChange={handleChange}
              required
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="pesel">PESEL *</label>
            <input
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

          <div className="form-group">
            <label htmlFor="membershipStatus">Status *</label>
            <select
              id="membershipStatus"
              name="membershipStatus"
              value={member.membershipStatus}
              onChange={handleChange}
              required
            >
              {MEMBERSHIP_STATUSES.map((s) => (
                <option key={s.value} value={s.value}>
                  {s.label}
                </option>
              ))}
            </select>
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="birthDate">Data urodzenia *</label>
            <input
              type="date"
              id="birthDate"
              name="birthDate"
              value={member.birthDate}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="joinDate">Data wstąpienia *</label>
            <input
              type="date"
              id="joinDate"
              name="joinDate"
              value={member.joinDate}
              onChange={handleChange}
              required
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              value={member.email}
              onChange={handleChange}
            />
          </div>

          <div className="form-group">
            <label htmlFor="phone">Telefon</label>
            <input
              type="tel"
              id="phone"
              name="phone"
              value={member.phone}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="form-group">
          <label htmlFor="address">Adres</label>
          <textarea
            id="address"
            name="address"
            value={member.address}
            onChange={handleChange}
            rows={2}
          />
        </div>

        <div className="form-group">
          <label htmlFor="boardPosition">Funkcja w zarządzie</label>
          <input
            type="text"
            id="boardPosition"
            name="boardPosition"
            value={member.boardPosition}
            onChange={handleChange}
          />
        </div>

        <div className="form-actions">
          <button type="button" onClick={() => navigate('/members')} className="btn">
            Anuluj
          </button>
          <button type="submit" disabled={saving} className="btn btn-primary">
            {saving ? 'Zapisywanie...' : 'Zapisz'}
          </button>
        </div>
      </form>
    </div>
  );
}
