// API Service Layer for Readdy Frontend
// Communicates with Laravel backend at http://localhost:8001/api/v1

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8001/api/v1';

// Types
export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  date_of_birth?: string;
  phone?: string;
  preferences?: any;
  reading_goals?: any;
  is_active: boolean;
  last_login_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Book {
  id: number;
  title: string;
  subtitle?: string;
  author: string;
  isbn?: string;
  publisher?: string;
  publication_date?: string;
  language: string;
  page_count?: number;
  word_count?: number;
  description?: string;
  excerpt?: string;
  cover_image?: string;
  price: string;
  original_price?: string;
  is_free: boolean;
  is_featured: boolean;
  is_bestseller: boolean;
  is_new_release: boolean;
  status: string;
  rating_average: string;
  rating_count: number;
  view_count: number;
  download_count: number;
  created_at: string;
  updated_at: string;
  categories: Category[];
  reviews: any[];
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  parent_id?: number;
  icon?: string;
  color?: string;
  is_active: boolean;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  errors?: any;
  meta?: any;
}

export interface PaginatedResponse<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: any[];
  next_page_url?: string;
  path: string;
  per_page: number;
  prev_page_url?: string;
  to: number;
  total: number;
}

// Order Types
export interface OrderItem {
  id: number;
  book_id: number;
  price: number;
  quantity: number;
  title: string;
  author?: string;
  cover_image?: string;
  metadata?: any;
}

export interface Order {
  id: number;
  order_number: string;
  user_id: number;
  payment_id?: number;
  total_amount: number;
  currency: string;
  status: string;
  metadata?: any;
  completed_at?: string;
  cancelled_at?: string;
  refunded_at?: string;
  created_at: string;
  updated_at: string;
  items: OrderItem[];
  receipt?: any;
}

export interface OrderNotification {
  id: number;
  type: string;
  title: string;
  message: string;
  data?: any;
  read_at?: string;
  created_at: string;
}

// API Client
class ApiClient {
  private baseURL: string;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> {
    const url = `${this.baseURL}${endpoint}`;
    
    const config: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    };

    // Add auth token if available
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('auth_token');
      if (token) {
        config.headers = {
          ...config.headers,
          'Authorization': `Bearer ${token}`,
        };
      }
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'API request failed');
      }

      return data;
    } catch (error) {
      console.error('API request error:', error);
      throw error;
    }
  }

  // Authentication
  async register(userData: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }): Promise<{ user: User; token: string }> {
    const response = await this.request<{ user: User; token: string }>('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    });
    return response.data;
  }

  async login(credentials: {
    email: string;
    password: string;
  }): Promise<{ user: User; token: string }> {
    const response = await this.request<{ user: User; token: string }>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(credentials),
    });
    return response.data;
  }

  async logout(): Promise<void> {
    await this.request('/auth/logout', {
      method: 'POST',
    });
  }

  async getCurrentUser(): Promise<User> {
    const response = await this.request<{ user: User }>('/auth/user');
    return response.data.user;
  }

  // Books
  async getBooks(filters: {
    search?: string;
    category?: string;
    price_min?: number;
    price_max?: number;
    rating?: number;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
  } = {}): Promise<PaginatedResponse<Book>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        params.append(key, value.toString());
      }
    });

    const response = await this.request<PaginatedResponse<Book>>(`/books?${params}`);
    return response.data;
  }

  async getBook(id: number): Promise<Book> {
    const response = await this.request<Book>(`/books/${id}`);
    return response.data;
  }

  async getFeaturedBooks(): Promise<Book[]> {
    const response = await this.request<Book[]>('/books/featured');
    return response.data;
  }

  async getBestsellers(): Promise<Book[]> {
    const response = await this.request<Book[]>('/books/bestsellers');
    return response.data;
  }

  async getNewReleases(): Promise<Book[]> {
    const response = await this.request<Book[]>('/books/new-releases');
    return response.data;
  }

  // Orders
  async getOrders(): Promise<ApiResponse<Order[]>> {
    return this.request<Order[]>('/orders');
  }

  async getOrder(orderId: number | string): Promise<ApiResponse<Order>> {
    return this.request<Order>(`/orders/${orderId}`);
  }

  async cancelOrder(orderId: number | string, reason?: string): Promise<ApiResponse<any>> {
    return this.request<any>(`/orders/${orderId}/cancel`, {
      method: 'POST',
      body: JSON.stringify({ reason }),
    });
  }

  async getOrderReceipt(orderId: number | string): Promise<ApiResponse<any>> {
    return this.request<any>(`/orders/${orderId}/receipt`);
  }

  async downloadOrderReceipt(orderId: number | string): Promise<ApiResponse<any>> {
    return this.request<any>(`/orders/${orderId}/receipt/download`);
  }

  // Order Notifications
  async getOrderNotifications(): Promise<ApiResponse<OrderNotification[]>> {
    return this.request<OrderNotification[]>('/orders/notifications');
  }

  async markOrderNotificationAsRead(notificationId: number | string): Promise<ApiResponse<any>> {
    return this.request<any>(`/orders/notifications/${notificationId}/read`, { method: 'POST' });
  }

  async getUnreadOrderNotificationsCount(): Promise<ApiResponse<{ count: number }>> {
    return this.request<{ count: number }>(`/orders/notifications/unread-count`);
  }
}

// Create and export API client instance
export const apiClient = new ApiClient(API_BASE_URL);

// Utility functions
export const isAuthenticated = (): boolean => {
  if (typeof window === 'undefined') return false;
  return !!localStorage.getItem('auth_token');
};

export const getAuthToken = (): string | null => {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem('auth_token');
};

export const setAuthToken = (token: string): void => {
  if (typeof window === 'undefined') return;
  localStorage.setItem('auth_token', token);
};

export const removeAuthToken = (): void => {
  if (typeof window === 'undefined') return;
  localStorage.removeItem('auth_token');
};

// React hooks for API calls
export const useApi = () => {
  return {
    register: apiClient.register.bind(apiClient),
    login: apiClient.login.bind(apiClient),
    logout: apiClient.logout.bind(apiClient),
    getCurrentUser: apiClient.getCurrentUser.bind(apiClient),
    getBooks: apiClient.getBooks.bind(apiClient),
    getBook: apiClient.getBook.bind(apiClient),
    getFeaturedBooks: apiClient.getFeaturedBooks.bind(apiClient),
    getBestsellers: apiClient.getBestsellers.bind(apiClient),
    getNewReleases: apiClient.getNewReleases.bind(apiClient),
    getOrders: apiClient.getOrders.bind(apiClient),
    getOrder: apiClient.getOrder.bind(apiClient),
    cancelOrder: apiClient.cancelOrder.bind(apiClient),
    getOrderReceipt: apiClient.getOrderReceipt.bind(apiClient),
    downloadOrderReceipt: apiClient.downloadOrderReceipt.bind(apiClient),
    getOrderNotifications: apiClient.getOrderNotifications.bind(apiClient),
    markOrderNotificationAsRead: apiClient.markOrderNotificationAsRead.bind(apiClient),
    getUnreadOrderNotificationsCount: apiClient.getUnreadOrderNotificationsCount.bind(apiClient),
  };
}; 