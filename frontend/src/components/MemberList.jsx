import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

const STATUS_LABELS = {
  active: 'Aktywny',
  inactive: 'Nieaktywny',
  honorary: 'Honorowy',
  supporting: 'Wspierający',
  youth: 'MDP',
  removed: 'Usunięty',
  deceased: 'Zmarły',
};

const STATUS_OPTIONS = [
  { value: '', label: 'Wszystkie statusy' },
  { value: 'active', label: 'Aktywny' },
  { value: 'inactive', label: 'Nieaktywny' },
  { value: 'honorary', label: 'Honorowy' },
  { value: 'supporting', label: 'Wspierający' },
  { value: 'youth', label: 'MDP' },
  { value: 'removed', label: 'Usunięty' },
  { value: 'deceased', label: 'Zmarły' },
];

export function MemberList() {
  const { hasRole } = useAuth();
  const canAdd = hasRole('ROLE_ADMIN');
  const canEdit = hasRole('ROLE_ADMIN') || hasRole('ROLE_PREZES') || hasRole('ROLE_NACZELNIK');

  const [members, setMembers] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // Filters
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const itemsPerPage = 20;

  const loadMembers = useCallback(async () => {
    try {
      setLoading(true);
      const params = {
        page,
        itemsPerPage,
      };

      if (search) {
        params.lastName = search;
      }
      if (statusFilter) {
        params.membershipStatus = statusFilter;
      }

      const data = await api.getMembers(params);
      setMembers(data.items);
      setTotalItems(data.totalItems);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [search, statusFilter, page]);

  useEffect(() => {
    loadMembers();
  }, [loadMembers]);

  // Reset to page 1 when filters change
  useEffect(() => {
    setPage(1);
  }, [search, statusFilter]);

  const handleDelete = async (id, name) => {
    if (!confirm(`Czy na pewno chcesz usunąć członka ${name}?`)) {
      return;
    }

    try {
      await api.deleteMember(id);
      loadMembers();
    } catch (err) {
      alert(`Błąd: ${err.message}`);
    }
  };

  const handleSearchChange = (e) => {
    setSearch(e.target.value);
  };

  const handleStatusChange = (e) => {
    setStatusFilter(e.target.value);
  };

  const totalPages = Math.ceil(totalItems / itemsPerPage);

  return (
    <div className="member-list">
      <div className="list-header">
        <h2>Ewidencja członków</h2>
        {canAdd && (
          <Link to="/members/new" className="btn btn-primary">
            + Dodaj członka
          </Link>
        )}
      </div>

      <div className="filters">
        <div className="filter-group">
          <input
            type="text"
            placeholder="Szukaj po nazwisku..."
            value={search}
            onChange={handleSearchChange}
            className="search-input"
          />
        </div>
        <div className="filter-group">
          <select value={statusFilter} onChange={handleStatusChange}>
            {STATUS_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>
        </div>
        <div className="filter-info">
          Znaleziono: {totalItems} {totalItems === 1 ? 'członek' : 'członków'}
        </div>
      </div>

      {error && <div className="error-message">{error}</div>}

      {loading ? (
        <div className="loading">Ładowanie...</div>
      ) : (
        <>
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
                    {canEdit && (
                      <Link to={`/members/${member.id}/edit`} className="btn btn-small">
                        Edytuj
                      </Link>
                    )}
                    {canAdd && (
                      <button
                        onClick={() => handleDelete(member.id, member.fullName)}
                        className="btn btn-small btn-danger"
                      >
                        Usuń
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {members.length === 0 && (
            <p className="empty-state">
              {search || statusFilter
                ? 'Brak członków spełniających kryteria wyszukiwania.'
                : 'Brak członków w ewidencji.'}
            </p>
          )}

          {totalPages > 1 && (
            <div className="pagination">
              <button
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
                className="btn btn-small"
              >
                &laquo; Poprzednia
              </button>
              <span className="pagination-info">
                Strona {page} z {totalPages}
              </span>
              <button
                onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                disabled={page === totalPages}
                className="btn btn-small"
              >
                Następna &raquo;
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
