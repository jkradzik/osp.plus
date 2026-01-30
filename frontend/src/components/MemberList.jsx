import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';

const STATUS_LABELS = {
  active: 'Aktywny',
  inactive: 'Nieaktywny',
  honorary: 'Honorowy',
  supporting: 'Wspierający',
  youth: 'MDP',
  removed: 'Usunięty',
  deceased: 'Zmarły',
};

export function MemberList() {
  const [members, setMembers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadMembers();
  }, []);

  const loadMembers = async () => {
    try {
      setLoading(true);
      const data = await api.getMembers();
      setMembers(data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id, name) => {
    if (!confirm(`Czy na pewno chcesz usunąć członka ${name}?`)) {
      return;
    }

    try {
      await api.deleteMember(id);
      setMembers(members.filter(m => m.id !== id));
    } catch (err) {
      alert(`Błąd: ${err.message}`);
    }
  };

  if (loading) return <div className="loading">Ładowanie...</div>;
  if (error) return <div className="error-message">{error}</div>;

  return (
    <div className="member-list">
      <div className="list-header">
        <h2>Ewidencja członków</h2>
        <Link to="/members/new" className="btn btn-primary">
          + Dodaj członka
        </Link>
      </div>

      <table>
        <thead>
          <tr>
            <th>Imię i nazwisko</th>
            <th>PESEL</th>
            <th>Status</th>
            <th>Data wstąpienia</th>
            <th>Funkcja</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          {members.map((member) => (
            <tr key={member.id}>
              <td>{member.fullName}</td>
              <td>{member.pesel}</td>
              <td>
                <span className={`status-badge status-${member.membershipStatus}`}>
                  {STATUS_LABELS[member.membershipStatus] || member.membershipStatus}
                </span>
              </td>
              <td>{new Date(member.joinDate).toLocaleDateString('pl-PL')}</td>
              <td>{member.boardPosition || '-'}</td>
              <td className="actions">
                <Link to={`/members/${member.id}/edit`} className="btn btn-small">
                  Edytuj
                </Link>
                <button
                  onClick={() => handleDelete(member.id, member.fullName)}
                  className="btn btn-small btn-danger"
                >
                  Usuń
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {members.length === 0 && (
        <p className="empty-state">Brak członków w ewidencji.</p>
      )}
    </div>
  );
}
