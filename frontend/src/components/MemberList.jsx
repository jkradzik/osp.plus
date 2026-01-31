import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Badge } from './ui/badge';
import { Alert, AlertDescription } from './ui/alert';
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

const STATUS_LABELS = {
  active: 'Aktywny',
  inactive: 'Nieaktywny',
  honorary: 'Honorowy',
  supporting: 'Wspierający',
  youth: 'MDP',
  removed: 'Usunięty',
  deceased: 'Zmarły',
};

const STATUS_VARIANTS = {
  active: 'success',
  inactive: 'muted',
  honorary: 'info',
  supporting: 'warning',
  youth: 'info',
  removed: 'destructive',
  deceased: 'destructive',
};

const STATUS_OPTIONS = [
  { value: 'all', label: 'Wszystkie statusy' },
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
  const [debouncedSearch, setDebouncedSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [page, setPage] = useState(1);
  const itemsPerPage = 20;

  // Debounce search input
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(search);
    }, 300);
    return () => clearTimeout(timer);
  }, [search]);

  const loadMembers = useCallback(async () => {
    try {
      setLoading(true);
      const params = {
        page,
        itemsPerPage,
      };

      if (debouncedSearch) {
        params.lastName = debouncedSearch;
      }
      if (statusFilter && statusFilter !== 'all') {
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
  }, [debouncedSearch, statusFilter, page]);

  useEffect(() => {
    loadMembers();
  }, [loadMembers]);

  // Reset to page 1 when filters change
  useEffect(() => {
    setPage(1);
  }, [debouncedSearch, statusFilter]);

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

  const totalPages = Math.ceil(totalItems / itemsPerPage);

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold">Ewidencja członków</h2>
        {canAdd && (
          <Button asChild>
            <Link to="/members/new">+ Dodaj członka</Link>
          </Button>
        )}
      </div>

      <div className="flex flex-wrap gap-4 items-center mb-6">
        <Input
          type="text"
          placeholder="Szukaj po nazwisku..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-64"
        />
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
          Znaleziono: {totalItems} {totalItems === 1 ? 'członek' : 'członków'}
        </span>
      </div>

      {error && (
        <Alert variant="destructive" className="mb-4">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      {loading ? (
        <div className="text-center py-8 text-muted-foreground">Ładowanie...</div>
      ) : (
        <>
          <div className="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Imię i nazwisko</TableHead>
                  <TableHead>PESEL</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Data wstąpienia</TableHead>
                  <TableHead>Funkcja</TableHead>
                  <TableHead>Akcje</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {members.map((member) => (
                  <TableRow key={member.id}>
                    <TableCell className="font-medium">{member.fullName}</TableCell>
                    <TableCell>{member.pesel}</TableCell>
                    <TableCell>
                      <Badge variant={STATUS_VARIANTS[member.membershipStatus] || 'secondary'}>
                        {STATUS_LABELS[member.membershipStatus] || member.membershipStatus}
                      </Badge>
                    </TableCell>
                    <TableCell>{new Date(member.joinDate).toLocaleDateString('pl-PL')}</TableCell>
                    <TableCell>{member.boardPosition || '-'}</TableCell>
                    <TableCell>
                      <div className="flex gap-2">
                        {canEdit && (
                          <Button variant="outline" size="sm" asChild>
                            <Link to={`/members/${member.id}/edit`}>Edytuj</Link>
                          </Button>
                        )}
                        {canAdd && (
                          <Button
                            variant="destructive"
                            size="sm"
                            onClick={() => handleDelete(member.id, member.fullName)}
                          >
                            Usuń
                          </Button>
                        )}
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>

          {members.length === 0 && (
            <p className="text-center py-8 text-muted-foreground">
              {debouncedSearch || statusFilter !== 'all'
                ? 'Brak członków spełniających kryteria wyszukiwania.'
                : 'Brak członków w ewidencji.'}
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
