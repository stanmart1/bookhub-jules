# Readdy Backend API Test Guide

## Base URL
`http://localhost:8001/api/v1`

## Authentication Endpoints

### 1. Register User
```bash
curl -X POST http://localhost:8001/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 2. Login User
```bash
curl -X POST http://localhost:8001/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 3. Get User Profile (Authenticated)
```bash
curl -X GET http://localhost:8001/api/v1/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 4. Logout User (Authenticated)
```bash
curl -X POST http://localhost:8001/api/v1/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Book Endpoints

### 1. Get All Books
```bash
curl -X GET "http://localhost:8001/api/v1/books?per_page=5"
```

### 2. Get Featured Books
```bash
curl -X GET http://localhost:8001/api/v1/books/featured
```

### 3. Get Bestsellers
```bash
curl -X GET http://localhost:8001/api/v1/books/bestsellers
```

### 4. Get New Releases
```bash
curl -X GET http://localhost:8001/api/v1/books/new-releases
```

### 5. Get Specific Book
```bash
curl -X GET http://localhost:8001/api/v1/books/1
```

### 6. Search Books
```bash
curl -X GET "http://localhost:8001/api/v1/books?search=atomic"
```

### 7. Filter by Category
```bash
curl -X GET "http://localhost:8001/api/v1/books?category=fiction"
```

### 8. Filter by Price Range
```bash
curl -X GET "http://localhost:8001/api/v1/books?price_min=10&price_max=20"
```

### 9. Filter by Rating
```bash
curl -X GET "http://localhost:8001/api/v1/books?rating=4"
```

## Expected Response Format

All API responses follow this format:
```json
{
  "success": true,
  "data": {},
  "message": "Success message",
  "errors": [],
  "meta": {
    "pagination": {},
    "filters": {},
    "sorting": {}
  }
}
```

## Sample Book Data

The database has been seeded with 5 sample books:
1. Tomorrow, and Tomorrow, and Tomorrow - Gabrielle Zevin
2. The Atlas Six - Olivie Blake
3. Lessons in Chemistry - Bonnie Garmus
4. Atomic Habits - James Clear
5. The Psychology of Money - Morgan Housel

## Sample Categories

The database has been seeded with 8 categories:
1. Fiction
2. Non-Fiction
3. Mystery
4. Romance
5. Science Fiction
6. Fantasy
7. Self-Help
8. Biography

## Server Status

✅ **Backend Server Running**: `http://localhost:8001`
✅ **API Endpoints Working**: All endpoints tested and functional
✅ **Database Seeded**: Sample data loaded successfully
✅ **Authentication Working**: Registration, login, and token-based auth functional
✅ **CORS Configured**: Ready for frontend integration 