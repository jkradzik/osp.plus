import { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

const TYPE_LABELS = {
  income: 'Przychód',
  expense: 'Koszt',
};

const TYPE_OPTIONS = [
  { value: '', label: 'Wszystkie typy' },
  { value: 'income', label: 'Przychody' },
  { value: 'expense', label: 'Koszty' },
];

const currentYear = new Date().getFullYear();
const YEAR_OPTIONS = [
  { value: '', label: 'Wszystkie lata' },
  ...Array.from({ length: 5 }, (_, i) => ({
    value: String(currentYear - i),
    label: String(currentYear - i),
  })),
];

export function FinancialList() {
  const { hasRole } = useAuth();
  const canEdit = hasRole('ROLE_ADMIN') || hasRole('ROLE_SKARBNIK');

  const [records, setRecords] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [categories, setCategories] = useState([]);
  const [summary, setSummary] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // Filters
  const [typeFilter, setTypeFilter] = useState('');
  const [yearFilter, setYearFilter] = useState(String(currentYear));
  const [page, setPage] = useState(1);
  const itemsPerPage = 20;

  // Form state
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({
    type: 'expense',
    category: '',
    amount: '',
    description: '',
    documentNumber: '',
    recordedAt: new Date().toISOString().split('T')[0],
  });
  const [formCategories, setFormCategories] = useState([]);

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const params = { page, itemsPerPage };

      if (typeFilter) {
        params.type = typeFilter;
      }

      if (yearFilter) {
        const startDate = `${yearFilter}-01-01`;
        const endDate = `${yearFilter}-12-31`;
        params['recordedAt[after]'] = startDate;
        params['recordedAt[before]'] = endDate;
      }

      const [recordsData, categoriesData, summaryData] = await Promise.all([
        api.getFinancialRecords(params),
        api.getFinancialCategories(),
        api.getFinancialSummary(yearFilter || null),
      ]);

      setRecords(recordsData.items);
      setTotalItems(recordsData.totalItems);
      setCategories(categoriesData);
      setSummary(summaryData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [typeFilter, yearFilter, page]);

  useEffect(() => {
    loadData();
  }, [loadData]);

  useEffect(() => {
    setPage(1);
  }, [typeFilter, yearFilter]);

  // Load categories for form when type changes
  useEffect(() => {
    const loadFormCategories = async () => {
      const cats = await api.getFinancialCategories(formData.type);
      setFormCategories(cats);
      setFormData((prev) => ({ ...prev, category: '' }));
    };
    loadFormCategories();
  }, [formData.type]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await api.createFinancialRecord({
        type: formData.type,
        category: `/api/financial_categories/${formData.category}`,
        amount: formData.amount,
        description: formData.description,
        documentNumber: formData.documentNumber || null,
        recordedAt: formData.recordedAt,
      });

      setShowForm(false);
      setFormData({
        type: 'expense',
        category: '',
        amount: '',
        description: '',
        documentNumber: '',
        recordedAt: new Date().toISOString().split('T')[0],
      });
      await loadData();
    } catch (err) {
      setError(err.message);
    }
  };

  const getCategoryName = (categoryIri) => {
    if (!categoryIri) return '-';
    const id = categoryIri.split('/').pop();
    const category = categories.find((c) => String(c.id) === id);
    return category ? category.name : categoryIri;
  };

  const formatAmount = (amount) => {
    return new Intl.NumberFormat('pl-PL', {
      style: 'currency',
      currency: 'PLN',
    }).format(parseFloat(amount));
  };

  const totalPages = Math.ceil(totalItems / itemsPerPage);

  return (
    <div className="financial-list">
      <div className="list-header">
        <h2>Ewidencja finansowa</h2>
        {canEdit && (
          <button onClick={() => setShowForm(!showForm)} className="btn btn-primary">
            {showForm ? 'Anuluj' : 'Dodaj operację'}
          </button>
        )}
      </div>

      {error && <div className="error-message">{error}</div>}

      {summary && (
        <div className="financial-summary">
          <div className="summary-cards">
            <div className="summary-card income">
              <h4>Przychody</h4>
              <p className="amount">{formatAmount(summary.totalIncome)}</p>
            </div>
            <div className="summary-card expense">
              <h4>Koszty</h4>
              <p className="amount">{formatAmount(summary.totalExpense)}</p>
            </div>
            <div className={`summary-card balance ${parseFloat(summary.balance) >= 0 ? 'positive' : 'negative'}`}>
              <h4>Bilans</h4>
              <p className="amount">{formatAmount(summary.balance)}</p>
            </div>
          </div>
        </div>
      )}

      {showForm && (
        <div className="member-form" style={{ marginBottom: '1.5rem' }}>
          <h3>Nowa operacja finansowa</h3>
          <form onSubmit={handleSubmit}>
            <div className="form-row">
              <div className="form-group">
                <label>Typ *</label>
                <select
                  value={formData.type}
                  onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                  required
                >
                  <option value="income">Przychód</option>
                  <option value="expense">Koszt</option>
                </select>
              </div>
              <div className="form-group">
                <label>Kategoria *</label>
                <select
                  value={formData.category}
                  onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                  required
                >
                  <option value="">Wybierz kategorię</option>
                  {formCategories.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Kwota (PLN) *</label>
                <input
                  type="number"
                  step="0.01"
                  min="0.01"
                  value={formData.amount}
                  onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                  required
                />
              </div>
              <div className="form-group">
                <label>Data operacji *</label>
                <input
                  type="date"
                  value={formData.recordedAt}
                  onChange={(e) => setFormData({ ...formData, recordedAt: e.target.value })}
                  required
                />
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Nr dokumentu</label>
                <input
                  type="text"
                  value={formData.documentNumber}
                  onChange={(e) => setFormData({ ...formData, documentNumber: e.target.value })}
                  placeholder="np. FV/2024/0123"
                />
              </div>
            </div>
            <div className="form-group">
              <label>Opis *</label>
              <textarea
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                required
                rows={3}
              />
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
          <select value={typeFilter} onChange={(e) => setTypeFilter(e.target.value)}>
            {TYPE_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>
        </div>
        <div className="filter-group">
          <select value={yearFilter} onChange={(e) => setYearFilter(e.target.value)}>
            {YEAR_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>
        </div>
        <div className="filter-info">
          Znaleziono: {totalItems} {totalItems === 1 ? 'operacja' : 'operacji'}
        </div>
      </div>

      {loading ? (
        <div className="loading">Ładowanie...</div>
      ) : (
        <>
          <table>
            <thead>
              <tr>
                <th>Data</th>
                <th>Typ</th>
                <th>Kategoria</th>
                <th>Opis</th>
                <th>Nr dokumentu</th>
                <th style={{ textAlign: 'right' }}>Kwota</th>
              </tr>
            </thead>
            <tbody>
              {records.map((r) => (
                <tr key={r.id || r['@id']}>
                  <td>{new Date(r.recordedAt).toLocaleDateString('pl-PL')}</td>
                  <td>
                    <span className={`status-badge status-${r.type === 'income' ? 'success' : 'danger'}`}>
                      {TYPE_LABELS[r.type]}
                    </span>
                  </td>
                  <td>{getCategoryName(r.category)}</td>
                  <td>{r.description?.substring(0, 50)}{r.description?.length > 50 ? '...' : ''}</td>
                  <td>{r.documentNumber || '-'}</td>
                  <td style={{ textAlign: 'right', fontWeight: 'bold', color: r.type === 'income' ? '#155724' : '#721c24' }}>
                    {r.type === 'income' ? '+' : '-'}{formatAmount(r.amount)}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {records.length === 0 && (
            <p className="empty-state">
              {typeFilter || yearFilter ? 'Brak operacji spełniających kryteria.' : 'Brak operacji finansowych.'}
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
