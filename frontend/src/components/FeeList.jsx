import { useState, useEffect } from 'react';
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

export function FeeList() {
  const [fees, setFees] = useState([]);
  const [overdueFees, setOverdueFees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [validating, setValidating] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [feesData, overdueData] = await Promise.all([
        api.getFees(),
        api.getOverdueFees(),
      ]);
      setFees(feesData);
      setOverdueFees(overdueData.items || []);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

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

  if (loading) return <div className="loading">Ładowanie...</div>;
  if (error) return <div className="error-message">{error}</div>;

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

      {overdueFees.length > 0 && (
        <div className="overdue-summary">
          <h3>Zaległe składki ({overdueFees.length})</h3>
          <ul>
            {overdueFees.map((fee) => (
              <li key={fee.id}>
                <strong>{fee.member_name}</strong> - {fee.year} rok - {fee.amount} zł
              </li>
            ))}
          </ul>
        </div>
      )}

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
        <p className="empty-state">Brak składek w systemie.</p>
      )}
    </div>
  );
}
