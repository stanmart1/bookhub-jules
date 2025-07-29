import { useState, useEffect } from 'react';
import { apiClient, Book } from '../lib/api';

export const useFeaturedBooks = () => {
  const [books, setBooks] = useState<Book[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchFeaturedBooks = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const data = await apiClient.getFeaturedBooks();
        setBooks(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to fetch featured books');
      } finally {
        setLoading(false);
      }
    };

    fetchFeaturedBooks();
  }, []);

  return { books, loading, error };
}; 