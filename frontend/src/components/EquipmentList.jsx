import { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

const CATEGORY_LABELS = {
  clothing: 'Odzież',
  protective: 'Ochronne',
  other: 'Inne',
};

export function EquipmentList() {
  const { hasRole } = useAuth();
  const canEdit = hasRole('ROLE_ADMIN') || hasRole('ROLE_NACZELNIK');

  const [equipment, setEquipment] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [members, setMembers] = useState([]);
  const [equipmentTypes, setEquipmentTypes] = useState([]);
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
    issuedAt: '',
    size: '',
    serialNumber: '',
    notes: '',
  });
  const [selectedType, setSelectedType] = useState(null);

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const params = { page, itemsPerPage };

      if (memberFilter) {
        params.member = memberFilter;
      }

      const [equipmentData, membersData, typesData] = await Promise.all([
        api.getEquipment(params),
        api.getMembers({ itemsPerPage: 100 }),
        api.getEquipmentTypes(),
      ]);

      setEquipment(equipmentData.items);
      setTotalItems(equipmentData.totalItems);
      setMembers(membersData.items);
      setEquipmentTypes(typesData);
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

  const handleTypeChange = (typeId) => {
    setFormData({ ...formData, type: typeId });
    const type = equipmentTypes.find((t) => String(t.id) === typeId);
    setSelectedType(type);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await api.createEquipment({
        member: `/api/members/${formData.member}`,
        type: `/api/equipment_dictionaries/${formData.type}`,
        issuedAt: formData.issuedAt,
        size: formData.size || null,
        serialNumber: formData.serialNumber || null,
        notes: formData.notes || null,
      });

      setShowForm(false);
      setFormData({
        member: '',
        type: '',
        issuedAt: '',
        size: '',
        serialNumber: '',
        notes: '',
      });
      setSelectedType(null);
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
    const type = equipmentTypes.find((t) => String(t.id) === id);
    return type ? type.name : typeIri;
  };

  const totalPages = Math.ceil(totalItems / itemsPerPage);

  return (
    <div className="equipment-list">
      <div className="list-header">
        <h2>Wyposażenie osobiste</h2>
        {canEdit && (
          <button onClick={() => setShowForm(!showForm)} className="btn btn-primary">
            {showForm ? 'Anuluj' : 'Przypisz wyposażenie'}
          </button>
        )}
      </div>

      {error && <div className="error-message">{error}</div>}

      {showForm && canEdit && (
        <div className="member-form" style={{ marginBottom: '1.5rem' }}>
          <h3>Nowe wyposażenie</h3>
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
                <label>Typ wyposażenia *</label>
                <select
                  value={formData.type}
                  onChange={(e) => handleTypeChange(e.target.value)}
                  required
                >
                  <option value="">Wybierz typ</option>
                  {equipmentTypes.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.name} ({CATEGORY_LABELS[t.category]})
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Data wydania *</label>
                <input
                  type="date"
                  value={formData.issuedAt}
                  onChange={(e) => setFormData({ ...formData, issuedAt: e.target.value })}
                  required
                />
              </div>
              {selectedType?.hasSizes && (
                <div className="form-group">
                  <label>Rozmiar</label>
                  <input
                    type="text"
                    value={formData.size}
                    onChange={(e) => setFormData({ ...formData, size: e.target.value })}
                    placeholder="np. L, XL, 43"
                  />
                </div>
              )}
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Numer seryjny</label>
                <input
                  type="text"
                  value={formData.serialNumber}
                  onChange={(e) => setFormData({ ...formData, serialNumber: e.target.value })}
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
          Znaleziono: {totalItems} {totalItems === 1 ? 'element' : 'elementów'}
        </div>
      </div>

      {loading ? (
        <div className="loading">Ładowanie...</div>
      ) : (
        <>
          <table>
            <thead>
              <tr>
                <th>Data wydania</th>
                <th>Członek</th>
                <th>Wyposażenie</th>
                <th>Rozmiar</th>
                <th>Nr seryjny</th>
              </tr>
            </thead>
            <tbody>
              {equipment.map((e) => (
                <tr key={e.id || e['@id']}>
                  <td>{new Date(e.issuedAt).toLocaleDateString('pl-PL')}</td>
                  <td>{getMemberName(e.member)}</td>
                  <td>{getTypeName(e.type)}</td>
                  <td>{e.size || '-'}</td>
                  <td>{e.serialNumber || '-'}</td>
                </tr>
              ))}
            </tbody>
          </table>

          {equipment.length === 0 && (
            <p className="empty-state">
              {memberFilter
                ? 'Brak wyposażenia dla wybranego członka.'
                : 'Brak wyposażenia w systemie.'}
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
