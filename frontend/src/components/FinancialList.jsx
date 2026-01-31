import { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { Badge } from './ui/badge';
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

const TYPE_LABELS = {
  income: 'Przychód',
  expense: 'Koszt',
};

const currentYear = new Date().getFullYear();
const YEAR_OPTIONS = [
  { value: 'all', label: 'Wszystkie lata' },
  ...Array.from({ length: 5 }, (_, i) => ({
    value: String(currentYear - i),
    label: String(currentYear - i),
  })),
];

const TYPE_OPTIONS = [
  { value: 'all', label: 'Wszystkie typy' },
  { value: 'income', label: 'Przychody' },
  { value: 'expense', label: 'Koszty' },
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
  const [typeFilter, setTypeFilter] = useState('all');
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

      if (typeFilter && typeFilter !== 'all') {
        params.type = typeFilter;
      }

      if (yearFilter && yearFilter !== 'all') {
        const startDate = `${yearFilter}-01-01`;
        const endDate = `${yearFilter}-12-31`;
        params['recordedAt[after]'] = startDate;
        params['recordedAt[before]'] = endDate;
      }

      const [recordsData, categoriesData, summaryData] = await Promise.all([
        api.getFinancialRecords(params),
        api.getFinancialCategories(),
        api.getFinancialSummary(yearFilter !== 'all' ? yearFilter : null),
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
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold">Ewidencja finansowa</h2>
        {canEdit && (
          <Button onClick={() => setShowForm(!showForm)}>
            {showForm ? 'Anuluj' : 'Dodaj operację'}
          </Button>
        )}
      </div>

      {error && (
        <Alert variant="destructive" className="mb-4">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      {summary && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <Card className="border-l-4 border-l-green-600">
            <CardContent className="pt-6">
              <p className="text-sm text-muted-foreground uppercase mb-1">Przychody</p>
              <p className="text-2xl font-bold text-green-700">{formatAmount(summary.totalIncome)}</p>
            </CardContent>
          </Card>
          <Card className="border-l-4 border-l-red-600">
            <CardContent className="pt-6">
              <p className="text-sm text-muted-foreground uppercase mb-1">Koszty</p>
              <p className="text-2xl font-bold text-red-700">{formatAmount(summary.totalExpense)}</p>
            </CardContent>
          </Card>
          <Card className="border-l-4 border-l-blue-600">
            <CardContent className="pt-6">
              <p className="text-sm text-muted-foreground uppercase mb-1">Bilans</p>
              <p className={`text-2xl font-bold ${parseFloat(summary.balance) >= 0 ? 'text-green-700' : 'text-red-700'}`}>
                {formatAmount(summary.balance)}
              </p>
            </CardContent>
          </Card>
        </div>
      )}

      {showForm && canEdit && (
        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Nowa operacja finansowa</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Typ *</Label>
                  <Select
                    value={formData.type}
                    onValueChange={(value) => setFormData({ ...formData, type: value })}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="income">Przychód</SelectItem>
                      <SelectItem value="expense">Koszt</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Kategoria *</Label>
                  <Select
                    value={formData.category}
                    onValueChange={(value) => setFormData({ ...formData, category: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Wybierz kategorię" />
                    </SelectTrigger>
                    <SelectContent>
                      {formCategories.map((c) => (
                        <SelectItem key={c.id} value={String(c.id)}>
                          {c.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Kwota (PLN) *</Label>
                  <Input
                    type="number"
                    step="0.01"
                    min="0.01"
                    value={formData.amount}
                    onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label>Data operacji *</Label>
                  <Input
                    type="date"
                    value={formData.recordedAt}
                    onChange={(e) => setFormData({ ...formData, recordedAt: e.target.value })}
                    required
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Nr dokumentu</Label>
                <Input
                  type="text"
                  value={formData.documentNumber}
                  onChange={(e) => setFormData({ ...formData, documentNumber: e.target.value })}
                  placeholder="np. FV/2024/0123"
                />
              </div>
              <div className="space-y-2">
                <Label>Opis *</Label>
                <Textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  required
                  rows={3}
                />
              </div>
              <div className="flex justify-end">
                <Button type="submit">Zapisz</Button>
              </div>
            </form>
          </CardContent>
        </Card>
      )}

      <div className="flex flex-wrap gap-4 items-center mb-6">
        <Select value={typeFilter} onValueChange={setTypeFilter}>
          <SelectTrigger className="w-48">
            <SelectValue placeholder="Typ operacji" />
          </SelectTrigger>
          <SelectContent>
            {TYPE_OPTIONS.map((opt) => (
              <SelectItem key={opt.value} value={opt.value}>
                {opt.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
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
        <span className="text-muted-foreground text-sm ml-auto">
          Znaleziono: {totalItems} {totalItems === 1 ? 'operacja' : 'operacji'}
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
                  <TableHead>Data</TableHead>
                  <TableHead>Typ</TableHead>
                  <TableHead>Kategoria</TableHead>
                  <TableHead>Opis</TableHead>
                  <TableHead>Nr dokumentu</TableHead>
                  <TableHead className="text-right">Kwota</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {records.map((r) => (
                  <TableRow key={r.id || r['@id']}>
                    <TableCell>{new Date(r.recordedAt).toLocaleDateString('pl-PL')}</TableCell>
                    <TableCell>
                      <Badge variant={r.type === 'income' ? 'success' : 'destructive'}>
                        {TYPE_LABELS[r.type]}
                      </Badge>
                    </TableCell>
                    <TableCell>{getCategoryName(r.category)}</TableCell>
                    <TableCell>
                      {r.description?.substring(0, 50)}
                      {r.description?.length > 50 ? '...' : ''}
                    </TableCell>
                    <TableCell>{r.documentNumber || '-'}</TableCell>
                    <TableCell className={`text-right font-bold ${r.type === 'income' ? 'text-green-700' : 'text-red-700'}`}>
                      {r.type === 'income' ? '+' : '-'}{formatAmount(r.amount)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>

          {records.length === 0 && (
            <p className="text-center py-8 text-muted-foreground">
              {typeFilter !== 'all' || yearFilter !== 'all'
                ? 'Brak operacji spełniających kryteria.'
                : 'Brak operacji finansowych.'}
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
