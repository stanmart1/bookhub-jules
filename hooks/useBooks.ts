import { useState, useEffect } from 'react';
import { apiClient, Book, PaginatedResponse } from '../lib/api';

interface UseBooksOptions {
  search?: string;
  category?: string;
  price_min?: number;
  price_max?: number;
  rating?: number;
  per_page?: number;
}

export const useBooks = (options: UseBooksOptions = {}) => {
  const [books, setBooks] = useState<PaginatedResponse<Book> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchBooks = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const data = await apiClient.getBooks(options);
        setBooks(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to fetch books');
      } finally {
        setLoading(false);
      }
    };

    fetchBooks();
  }, [options.search, options.category, options.price_min, options.price_max, options.rating, options.per_page]);

  return { books, loading, error };
}; 