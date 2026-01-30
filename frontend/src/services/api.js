const API_URL = import.meta.env.VITE_API_URL || 'https://osp-plus.ddev.site';

class ApiService {
  #token = null;

  constructor() {
    this.#token = localStorage.getItem('jwt_token');
  }

  setToken(token) {
    this.#token = token;
    if (token) {
      localStorage.setItem('jwt_token', token);
    } else {
      localStorage.removeItem('jwt_token');
    }
  }

  getToken() {
    return this.#token;
  }

  isAuthenticated() {
    return !!this.#token;
  }

  async #request(endpoint, options = {}) {
    const headers = {
      'Accept': 'application/ld+json',
      ...options.headers,
    };

    if (this.#token) {
      headers['Authorization'] = `Bearer ${this.#token}`;
    }

    // Only set default Content-Type if not already specified in options
    if (options.body && !(options.body instanceof FormData) && !options.headers?.['Content-Type']) {
      headers['Content-Type'] = 'application/ld+json';
    }

    const response = await fetch(`${API_URL}${endpoint}`, {
      ...options,
      headers,
    });

    if (response.status === 401) {
      this.setToken(null);
      window.location.href = '/login';
      throw new Error('Unauthorized');
    }

    if (!response.ok) {
      const error = await response.json().catch(() => ({}));
      throw new Error(error.detail || error.message || `HTTP ${response.status}`);
    }

    if (response.status === 204) {
      return null;
    }

    return response.json();
  }

  // Auth
  async login(email, password) {
    const response = await fetch(`${API_URL}/api/login_check`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      throw new Error('Nieprawidłowy email lub hasło');
    }

    const data = await response.json();
    this.setToken(data.token);
    return data;
  }

  logout() {
    this.setToken(null);
  }

  // Members
  async getMembers(params = {}) {
    const queryString = this.#buildQueryString(params);
    const data = await this.#request(`/api/members${queryString}`);
    return {
      items: data.member || data['hydra:member'] || [],
      totalItems: data['hydra:totalItems'] || data.totalItems || 0,
      view: data['hydra:view'] || data.view || null,
    };
  }

  #buildQueryString(params) {
    const filtered = Object.entries(params).filter(([_, v]) => v !== '' && v !== null && v !== undefined);
    if (filtered.length === 0) return '';
    return '?' + filtered.map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`).join('&');
  }

  async getMember(id) {
    return this.#request(`/api/members/${id}`);
  }

  async createMember(member) {
    return this.#request('/api/members', {
      method: 'POST',
      headers: { 'Content-Type': 'application/ld+json' },
      body: JSON.stringify(member),
    });
  }

  async updateMember(id, member) {
    return this.#request(`/api/members/${id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/merge-patch+json' },
      body: JSON.stringify(member),
    });
  }

  async deleteMember(id) {
    return this.#request(`/api/members/${id}`, {
      method: 'DELETE',
    });
  }

  // Membership Fees
  async getFees(params = {}) {
    const queryString = this.#buildQueryString(params);
    const data = await this.#request(`/api/membership_fees${queryString}`);
    return {
      items: data.member || data['hydra:member'] || [],
      totalItems: data['hydra:totalItems'] || data.totalItems || 0,
      view: data['hydra:view'] || data.view || null,
    };
  }

  async validateOverdueFees() {
    return fetch(`${API_URL}/api/membership-fees/validate-overdue`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.#token}`,
        'Accept': 'application/json',
      },
    }).then(r => r.json());
  }

  async getOverdueFees() {
    return fetch(`${API_URL}/api/membership-fees/overdue`, {
      headers: {
        'Authorization': `Bearer ${this.#token}`,
        'Accept': 'application/json',
      },
    }).then(r => r.json());
  }
}

export const api = new ApiService();
