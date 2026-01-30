import { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';

const STATUS_LABELS = {
  unpaid: 'Nieopłacona',
  paid: 'Opłacona',
  overdue: 'Zaległa',
  exempt: 'Zwolniony',
  not_applicable: 'Nie dotyczy',
};

const STATUS_CLASSES = {
  unpaid: 'warning',
  paid: 'success',
  overdue: 'danger',
  exempt: 'info',
  not_applicable: 'muted',
};

const STATUS_OPTIONS = [
  { value: '', label: 'Wszystkie statusy' },
  { value: 'unpaid', label: 'Nieopłacona' },
  { value: 'paid', label: 'Opłacona' },
  { value: 'overdue', label: 'Zaległa' },
  { value: 'exempt', label: 'Zwolniony' },
  { value: 'not_applicable', label: 'Nie dotyczy' },
];

// Generate year options (current year back to 2020)
const currentYear = new Date().getFullYear();
const YEAR_OPTIONS = [
  { value: '', label: 'Wszystkie lata' },
  ...Array.from({ length: currentYear - 2019 }, (_, i) => ({
    value: String(currentYear - i),
    label: String(currentYear - i),
  })),
];

export function FeeList() {
  const [fees, setFees] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [overdueFees, setOverdueFees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [validating, setValidating] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  // Filters
  const [yearFilter, setYearFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const itemsPerPage = 20;

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const params = {
        page,
        itemsPerPage,
      };

      if (yearFilter) {
        params.year = yearFilter;
      }
      if (statusFilter) {
        params.status = statusFilter;
      }

      const [feesData, overdueData] = await Promise.all([
        api.getFees(params),
        api.getOverdueFees(),
      ]);

      setFees(feesData.items);
      setTotalItems(feesData.totalItems);
      setOverdueFees(overdueData.items || []);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [yearFilter, statusFilter, page]);

  useEffect(() => {
    loadData();
  }, [loadData]);

  // Reset to page 1 when filters change
  useEffect(() => {
    setPage(1);
  }, [yearFilter, statusFilter]);

  const handleValidateOverdue = async () => {
    try {
      setValidating(true);
      setMessage('');
      const result = await api.validateOverdueFees();
      setMessage(result.message);
      await loadData();
    } catch (err) {
      setError(err.message);
    } finally {
      setValidating(false);
    }
  };

  const totalPages = Math.ceil(totalItems / itemsPerPage);

  return (
    <div className="fee-list">
      <div className="list-header">
        <h2>Składki członkowskie</h2>
        <button
          onClick={handleValidateOverdue}
          disabled={validating}
          className="btn btn-warning"
        >
          {validating ? 'Sprawdzanie...' : 'Oznacz zaległe składki'}
        </button>
      </div>

      {message && <div className="success-message">{message}</div>}
      {error && <div className="error-message">{error}</div>}

      <div className="filters">
        <div className="filter-group">
          <select value={yearFilter} onChange={(e) => setYearFilter(e.target.value)}>
            {YEAR_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>
        </div>
        <div className="filter-group">
          <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}>
            {STATUS_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>
        </div>
        <div className="filter-info">
          Znaleziono: {totalItems} {totalItems === 1 ? 'składka' : 'składek'}
        </div>
      </div>

      {overdueFees.length > 0 && !statusFilter && !yearFilter && (
        <div className="overdue-summary">
          <h3>Zaległe składki ({overdueFees.length})</h3>
          <ul>
            {overdueFees.slice(0, 5).map((fee) => (
              <li key={fee.id}>
                <strong>{fee.member_name}</strong> - {fee.year} rok - {fee.amount} zł
              </li>
            ))}
            {overdueFees.length > 5 && (
              <li className="more-items">...i {overdueFees.length - 5} więcej</li>
            )}
          </ul>
        </div>
      )}

      {loading ? (
        <div className="loading">Ładowanie...</div>
      ) : (
        <>
          <h3>Wszystkie składki</h3>
          <table>
            <thead>
              <tr>
                <th>Rok</th>
                <th>Członek</th>
                <th>Kwota</th>
                <th>Status</th>
                <th>Data opłacenia</th>
              </tr>
            </thead>
            <tbody>
              {fees.map((fee) => (
                <tr key={fee.id || fee['@id']}>
                  <td>{fee.year}</td>
                  <td>{fee.member?.fullName || fee.member}</td>
                  <td>{fee.amount} zł</td>
                  <td>
                    <span className={`status-badge status-${STATUS_CLASSES[fee.status] || 'default'}`}>
                      {STATUS_LABELS[fee.status] || fee.status}
                    </span>
                  </td>
                  <td>
                    {fee.paidAt
                      ? new Date(fee.paidAt).toLocaleDateString('pl-PL')
                      : '-'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {fees.length === 0 && (
            <p className="empty-state">
              {yearFilter || statusFilter
                ? 'Brak składek spełniających kryteria wyszukiwania.'
                : 'Brak składek w systemie.'}
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
