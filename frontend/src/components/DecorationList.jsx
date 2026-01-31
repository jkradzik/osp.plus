import { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';

const CATEGORY_LABELS = {
  osp: 'OSP',
  state: 'Państwowe',
  other: 'Inne',
};

export function DecorationList() {
  const [decorations, setDecorations] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [members, setMembers] = useState([]);
  const [decorationTypes, setDecorationTypes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // Filters
  const [memberFilter, setMemberFilter] = useState('');
  const [page, setPage] = useState(1);
  const itemsPerPage = 20;

  // Form state
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({
    member: '',
    type: '',
    awardedAt: '',
    awardedBy: '',
    certificateNumber: '',
    notes: '',
  });

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const params = { page, itemsPerPage };

      if (memberFilter) {
        params.member = memberFilter;
      }

      const [decorationsData, membersData, typesData] = await Promise.all([
        api.getDecorations(params),
        api.getMembers({ itemsPerPage: 100 }),
        api.getDecorationTypes(),
      ]);

      setDecorations(decorationsData.items);
      setTotalItems(decorationsData.totalItems);
      setMembers(membersData.items);
      setDecorationTypes(typesData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [memberFilter, page]);

  useEffect(() => {
    loadData();
  }, [loadData]);

  useEffect(() => {
    setPage(1);
  }, [memberFilter]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await api.createDecoration({
        member: `/api/members/${formData.member}`,
        type: `/api/decoration_dictionaries/${formData.type}`,
        awardedAt: formData.awardedAt,
        awardedBy: formData.awardedBy || null,
        certificateNumber: formData.certificateNumber || null,
        notes: formData.notes || null,
      });

      setShowForm(false);
      setFormData({
        member: '',
        type: '',
        awardedAt: '',
        awardedBy: '',
        certificateNumber: '',
        notes: '',
      });
      await loadData();
    } catch (err) {
      setError(err.message);
    }
  };

  const getMemberName = (memberIri) => {
    if (!memberIri) return '-';
    const id = memberIri.split('/').pop();
    const member = members.find((m) => String(m.id) === id);
    return member ? member.fullName : memberIri;
  };

  const getTypeName = (typeIri) => {
    if (!typeIri) return '-';
    const id = typeIri.split('/').pop();
    const type = decorationTypes.find((t) => String(t.id) === id);
    return type ? type.name : typeIri;
  };

  const totalPages = Math.ceil(totalItems / itemsPerPage);

  return (
    <div className="decoration-list">
      <div className="list-header">
        <h2>Odznaczenia</h2>
        <button onClick={() => setShowForm(!showForm)} className="btn btn-primary">
          {showForm ? 'Anuluj' : 'Dodaj odznaczenie'}
        </button>
      </div>

      {error && <div className="error-message">{error}</div>}

      {showForm && (
        <div className="member-form" style={{ marginBottom: '1.5rem' }}>
          <h3>Nowe odznaczenie</h3>
          <form onSubmit={handleSubmit}>
            <div className="form-row">
              <div className="form-group">
                <label>Członek *</label>
                <select
                  value={formData.member}
                  onChange={(e) => setFormData({ ...formData, member: e.target.value })}
                  required
                >
                  <option value="">Wybierz członka</option>
                  {members.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.fullName}
                    </option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Typ odznaczenia *</label>
                <select
                  value={formData.type}
                  onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                  required
                >
                  <option value="">Wybierz typ</option>
                  {decorationTypes.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.name} ({CATEGORY_LABELS[t.category]})
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Data nadania *</label>
                <input
                  type="date"
                  value={formData.awardedAt}
                  onChange={(e) => setFormData({ ...formData, awardedAt: e.target.value })}
                  required
                />
              </div>
              <div className="form-group">
                <label>Nadane przez</label>
                <input
                  type="text"
                  value={formData.awardedBy}
                  onChange={(e) => setFormData({ ...formData, awardedBy: e.target.value })}
                  placeholder="np. Zarząd Główny ZOSP RP"
                />
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Numer legitymacji</label>
                <input
                  type="text"
                  value={formData.certificateNumber}
                  onChange={(e) => setFormData({ ...formData, certificateNumber: e.target.value })}
                />
              </div>
              <div className="form-group">
                <label>Notatki</label>
                <input
                  type="text"
                  value={formData.notes}
                  onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                />
              </div>
            </div>
            <div className="form-actions">
              <button type="submit" className="btn btn-primary">
                Zapisz
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="filters">
        <div className="filter-group">
          <select value={memberFilter} onChange={(e) => setMemberFilter(e.target.value)}>
            <option value="">Wszyscy członkowie</option>
            {members.map((m) => (
              <option key={m.id} value={m.id}>
                {m.fullName}
              </option>
            ))}
          </select>
        </div>
        <div className="filter-info">
          Znaleziono: {totalItems} {totalItems === 1 ? 'odznaczenie' : 'odznaczeń'}
        </div>
      </div>

      {loading ? (
        <div className="loading">Ładowanie...</div>
      ) : (
        <>
          <table>
            <thead>
              <tr>
                <th>Data nadania</th>
                <th>Członek</th>
                <th>Odznaczenie</th>
                <th>Nadane przez</th>
                <th>Nr legitymacji</th>
              </tr>
            </thead>
            <tbody>
              {decorations.map((d) => (
                <tr key={d.id || d['@id']}>
                  <td>{new Date(d.awardedAt).toLocaleDateString('pl-PL')}</td>
                  <td>{getMemberName(d.member)}</td>
                  <td>{getTypeName(d.type)}</td>
                  <td>{d.awardedBy || '-'}</td>
                  <td>{d.certificateNumber || '-'}</td>
                </tr>
              ))}
            </tbody>
          </table>

          {decorations.length === 0 && (
            <p className="empty-state">
              {memberFilter ? 'Brak odznaczeń dla wybranego członka.' : 'Brak odznaczeń w systemie.'}
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
