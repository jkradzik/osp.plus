import { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';
import { Button } from './ui/button';
import { Badge } from './ui/badge';
import { Alert, AlertDescription, AlertTitle } from './ui/alert';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from './ui/table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './ui/select';
import { AlertTriangle } from 'lucide-react';

const STATUS_LABELS = {
  unpaid: 'Nieopłacona',
  paid: 'Opłacona',
  overdue: 'Zaległa',
  exempt: 'Zwolniony',
  not_applicable: 'Nie dotyczy',
};

const STATUS_VARIANTS = {
  unpaid: 'warning',
  paid: 'success',
  overdue: 'destructive',
  exempt: 'info',
  not_applicable: 'muted',
};

const STATUS_OPTIONS = [
  { value: 'all', label: 'Wszystkie statusy' },
  { value: 'unpaid', label: 'Nieopłacona' },
  { value: 'paid', label: 'Opłacona' },
  { value: 'overdue', label: 'Zaległa' },
  { value: 'exempt', label: 'Zwolniony' },
  { value: 'not_applicable', label: 'Nie dotyczy' },
];

// Generate year options (current year back to 2020)
const currentYear = new Date().getFullYear();
const YEAR_OPTIONS = [
  { value: 'all', label: 'Wszystkie lata' },
  ...Array.from({ length: currentYear - 2019 }, (_, i) => ({
    value: String(currentYear - i),
    label: String(currentYear - i),
  })),
];

export function FeeList() {
  const { hasRole } = useAuth();
  const canEdit = hasRole('ROLE_ADMIN') || hasRole('ROLE_SKARBNIK');

  const [fees, setFees] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [members, setMembers] = useState([]);
  const [overdueFees, setOverdueFees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [validating, setValidating] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  // Filters
  const [yearFilter, setYearFilter] = useState('all');
  const [statusFilter, setStatusFilter] = useState('all');
  const [page, setPage] = useState(1);
  const itemsPerPage = 20;

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const params = {
        page,
        itemsPerPage,
      };

      if (yearFilter && yearFilter !== 'all') {
        params.year = yearFilter;
      }
      if (statusFilter && statusFilter !== 'all') {
        params.status = statusFilter;
      }

      const [feesData, overdueData, membersData] = await Promise.all([
        api.getFees(params),
        api.getOverdueFees(),
        api.getMembers({ itemsPerPage: 500 }),
      ]);

      setFees(feesData.items);
      setTotalItems(feesData.totalItems);
      setMembers(membersData.items);
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

  const getMemberName = (memberIri) => {
    if (!memberIri) return '-';
    if (typeof memberIri === 'object' && memberIri.fullName) {
      return memberIri.fullName;
    }
    const id = String(memberIri).split('/').pop();
    const member = members.find((m) => String(m.id) === id);
    return member ? member.fullName : memberIri;
  };

  const totalPages = Math.ceil(totalItems / itemsPerPage);

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold">Składki członkowskie</h2>
        {canEdit && (
          <Button
            onClick={handleValidateOverdue}
            disabled={validating}
            variant="warning"
          >
            {validating ? 'Sprawdzanie...' : 'Oznacz zaległe składki'}
          </Button>
        )}
      </div>

      {message && (
        <Alert className="mb-4">
          <AlertDescription>{message}</AlertDescription>
        </Alert>
      )}
      {error && (
        <Alert variant="destructive" className="mb-4">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="flex flex-wrap gap-4 items-center mb-6">
        <Select value={yearFilter} onValueChange={setYearFilter}>
          <SelectTrigger className="w-48">
            <SelectValue placeholder="Wybierz rok" />
          </SelectTrigger>
          <SelectContent>
            {YEAR_OPTIONS.map((opt) => (
              <SelectItem key={opt.value} value={opt.value}>
                {opt.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
        <Select value={statusFilter} onValueChange={setStatusFilter}>
          <SelectTrigger className="w-48">
            <SelectValue placeholder="Wybierz status" />
          </SelectTrigger>
          <SelectContent>
            {STATUS_OPTIONS.map((opt) => (
              <SelectItem key={opt.value} value={opt.value}>
                {opt.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
        <span className="text-muted-foreground text-sm ml-auto">
          Znaleziono: {totalItems} {totalItems === 1 ? 'składka' : 'składek'}
        </span>
      </div>

      {overdueFees.length > 0 && statusFilter === 'all' && yearFilter === 'all' && (
        <Alert variant="warning" className="mb-6">
          <AlertTriangle className="h-4 w-4" />
          <AlertTitle>Zaległe składki ({overdueFees.length})</AlertTitle>
          <AlertDescription>
            <ul className="mt-2 space-y-1">
              {overdueFees.slice(0, 5).map((fee) => (
                <li key={fee.id}>
                  <strong>{fee.member_name}</strong> - {fee.year} rok - {fee.amount} zł
                </li>
              ))}
              {overdueFees.length > 5 && (
                <li className="italic opacity-70">...i {overdueFees.length - 5} więcej</li>
              )}
            </ul>
          </AlertDescription>
        </Alert>
      )}

      {loading ? (
        <div className="text-center py-8 text-muted-foreground">Ładowanie...</div>
      ) : (
        <>
          <h3 className="text-lg font-semibold mb-4">Wszystkie składki</h3>
          <div className="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Rok</TableHead>
                  <TableHead>Członek</TableHead>
                  <TableHead>Kwota</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Data opłacenia</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {fees.map((fee) => (
                  <TableRow key={fee.id || fee['@id']}>
                    <TableCell className="font-medium">{fee.year}</TableCell>
                    <TableCell>{getMemberName(fee.member)}</TableCell>
                    <TableCell>{fee.amount} zł</TableCell>
                    <TableCell>
                      <Badge variant={STATUS_VARIANTS[fee.status] || 'secondary'}>
                        {STATUS_LABELS[fee.status] || fee.status}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      {fee.paidAt
                        ? new Date(fee.paidAt).toLocaleDateString('pl-PL')
                        : '-'}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>

          {fees.length === 0 && (
            <p className="text-center py-8 text-muted-foreground">
              {yearFilter !== 'all' || statusFilter !== 'all'
                ? 'Brak składek spełniających kryteria wyszukiwania.'
                : 'Brak składek w systemie.'}
            </p>
          )}

          {totalPages > 1 && (
            <div className="flex justify-center items-center gap-4 mt-6 pt-4 border-t">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
              >
                &laquo; Poprzednia
              </Button>
              <span className="text-sm text-muted-foreground">
                Strona {page} z {totalPages}
              </span>
              <Button
                variant="outline"
                size="sm"
                onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                disabled={page === totalPages}
              >
                Następna &raquo;
              </Button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
