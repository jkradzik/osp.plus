import { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Alert, AlertDescription } from './ui/alert';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
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

const CATEGORY_LABELS = {
  osp: 'OSP',
  state: 'Państwowe',
  other: 'Inne',
};

export function DecorationList() {
  const { hasRole } = useAuth();
  const canEdit = hasRole('ROLE_ADMIN') || hasRole('ROLE_PREZES');

  const [decorations, setDecorations] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [members, setMembers] = useState([]);
  const [decorationTypes, setDecorationTypes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // Filters
  const [memberFilter, setMemberFilter] = useState('all');
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

      if (memberFilter && memberFilter !== 'all') {
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
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold">Odznaczenia</h2>
        {canEdit && (
          <Button onClick={() => setShowForm(!showForm)}>
            {showForm ? 'Anuluj' : 'Dodaj odznaczenie'}
          </Button>
        )}
      </div>

      {error && (
        <Alert variant="destructive" className="mb-4">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      {showForm && canEdit && (
        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Nowe odznaczenie</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Członek *</Label>
                  <Select
                    value={formData.member}
                    onValueChange={(value) => setFormData({ ...formData, member: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Wybierz członka" />
                    </SelectTrigger>
                    <SelectContent>
                      {members.map((m) => (
                        <SelectItem key={m.id} value={String(m.id)}>
                          {m.fullName}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Typ odznaczenia *</Label>
                  <Select
                    value={formData.type}
                    onValueChange={(value) => setFormData({ ...formData, type: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Wybierz typ" />
                    </SelectTrigger>
                    <SelectContent>
                      {decorationTypes.map((t) => (
                        <SelectItem key={t.id} value={String(t.id)}>
                          {t.name} ({CATEGORY_LABELS[t.category]})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Data nadania *</Label>
                  <Input
                    type="date"
                    value={formData.awardedAt}
                    onChange={(e) => setFormData({ ...formData, awardedAt: e.target.value })}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label>Nadane przez</Label>
                  <Input
                    type="text"
                    value={formData.awardedBy}
                    onChange={(e) => setFormData({ ...formData, awardedBy: e.target.value })}
                    placeholder="np. Zarząd Główny ZOSP RP"
                  />
                </div>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Numer legitymacji</Label>
                  <Input
                    type="text"
                    value={formData.certificateNumber}
                    onChange={(e) => setFormData({ ...formData, certificateNumber: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Notatki</Label>
                  <Input
                    type="text"
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                  />
                </div>
              </div>
              <div className="flex justify-end">
                <Button type="submit">Zapisz</Button>
              </div>
            </form>
          </CardContent>
        </Card>
      )}

      <div className="flex flex-wrap gap-4 items-center mb-6">
        <Select value={memberFilter} onValueChange={setMemberFilter}>
          <SelectTrigger className="w-64">
            <SelectValue placeholder="Wybierz członka" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">Wszyscy członkowie</SelectItem>
            {members.map((m) => (
              <SelectItem key={m.id} value={String(m.id)}>
                {m.fullName}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
        <span className="text-muted-foreground text-sm ml-auto">
          Znaleziono: {totalItems} {totalItems === 1 ? 'odznaczenie' : 'odznaczeń'}
        </span>
      </div>

      {loading ? (
        <div className="text-center py-8 text-muted-foreground">Ładowanie...</div>
      ) : (
        <>
          <div className="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Data nadania</TableHead>
                  <TableHead>Członek</TableHead>
                  <TableHead>Odznaczenie</TableHead>
                  <TableHead>Nadane przez</TableHead>
                  <TableHead>Nr legitymacji</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {decorations.map((d) => (
                  <TableRow key={d.id || d['@id']}>
                    <TableCell>{new Date(d.awardedAt).toLocaleDateString('pl-PL')}</TableCell>
                    <TableCell className="font-medium">{getMemberName(d.member)}</TableCell>
                    <TableCell>{getTypeName(d.type)}</TableCell>
                    <TableCell>{d.awardedBy || '-'}</TableCell>
                    <TableCell>{d.certificateNumber || '-'}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>

          {decorations.length === 0 && (
            <p className="text-center py-8 text-muted-foreground">
              {memberFilter !== 'all' ? 'Brak odznaczeń dla wybranego członka.' : 'Brak odznaczeń w systemie.'}
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
