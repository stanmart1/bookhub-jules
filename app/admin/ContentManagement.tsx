
'use client';

import { useState } from 'react';

export default function ContentManagement() {
  const [activeSection, setActiveSection] = useState('books');
  const [showAddModal, setShowAddModal] = useState(false);
  const [modalType, setModalType] = useState('book');
  const [bookType, setBookType] = useState('ebook');

  const books = [
    {
      id: 1,
      title: 'The Psychology of Money',
      author: 'Morgan Housel',
      category: 'Finance',
      price: 19.99,
      status: 'published',
      sales: 1247,
      rating: 4.8,
      dateAdded: '2024-01-15',
      inventory: 156,
      featured: true,
      cover: 'https://readdy.ai/api/search-image?query=psychology%20of%20money%20book%20cover%20design%2C%20financial%20concepts%2C%20modern%20minimalist%20style%2C%20professional%20layout%2C%20clean%20typography&width=60&height=90&seq=content-1&orientation=portrait'
    },
    {
      id: 2,
      title: 'Atomic Habits',
      author: 'James Clear',
      category: 'Self-Help',
      price: 16.99,
      status: 'published',
      sales: 2156,
      rating: 4.9,
      dateAdded: '2024-01-20',
      inventory: 89,
      featured: true,
      cover: 'https://readdy.ai/api/search-image?query=atomic%20habits%20book%20cover%20design%2C%20habit%20formation%20concept%2C%20scientific%20approach%2C%20modern%20design%2C%20motivational%20theme&width=60&height=90&seq=content-2&orientation=portrait'
    },
    {
      id: 3,
      title: 'The Midnight Library',
      author: 'Matt Haig',
      category: 'Fiction',
      price: 14.99,
      status: 'draft',
      sales: 0,
      rating: 0,
      dateAdded: '2024-02-01',
      inventory: 0,
      featured: false,
      cover: 'https://readdy.ai/api/search-image?query=midnight%20library%20book%20cover%20design%2C%20mystical%20library%20setting%2C%20dark%20blue%20tones%2C%20magical%20atmosphere%2C%20literary%20fiction%20style&width=60&height=90&seq=content-3&orientation=portrait'
    },
    {
      id: 4,
      title: 'Dune',
      author: 'Frank Herbert',
      category: 'Sci-Fi',
      price: 18.99,
      status: 'published',
      sales: 987,
      rating: 4.7,
      dateAdded: '2024-01-10',
      inventory: 234,
      featured: false,
      cover: 'https://readdy.ai/api/search-image?query=dune%20book%20cover%20design%2C%20desert%20planet%20landscape%2C%20futuristic%20science%20fiction%2C%20epic%20space%20opera%2C%20golden%20sand%20dunes&width=60&height=90&seq=content-4&orientation=portrait'
    }
  ];

  const categories = [
    { id: 1, name: 'Fiction', bookCount: 245, color: 'bg-blue-100 text-blue-800', description: 'Literary fiction and novels' },
    { id: 2, name: 'Non-Fiction', bookCount: 189, color: 'bg-green-100 text-green-800', description: 'Educational and informational books' },
    { id: 3, name: 'Self-Help', bookCount: 156, color: 'bg-purple-100 text-purple-800', description: 'Personal development and improvement' },
    { id: 4, name: 'Mystery', bookCount: 98, color: 'bg-yellow-100 text-yellow-800', description: 'Suspense and detective stories' },
    { id: 5, name: 'Romance', bookCount: 87, color: 'bg-pink-100 text-pink-800', description: 'Love stories and romantic fiction' },
    { id: 6, name: 'Sci-Fi', bookCount: 76, color: 'bg-indigo-100 text-indigo-800', description: 'Science fiction and futuristic tales' }
  ];

  const authors = [
    {
      id: 1,
      name: 'Morgan Housel',
      email: 'morgan@example.com',
      booksCount: 3,
      totalSales: 1247,
      revenue: 24940,
      joinDate: '2023-12-01',
      status: 'active',
      bio: 'Financial writer and behavioral economist',
      avatar: 'https://readdy.ai/api/search-image?query=professional%20author%20portrait%2C%20financial%20writer%2C%20confident%20expression%2C%20modern%20headshot%2C%20business%20casual&width=40&height=40&seq=author-1&orientation=squarish'
    },
    {
      id: 2,
      name: 'James Clear',
      email: 'james@example.com',
      booksCount: 2,
      totalSales: 2156,
      revenue: 36648,
      joinDate: '2023-11-15',
      status: 'active',
      bio: 'Author and speaker focused on habits and decision making',
      avatar: 'https://readdy.ai/api/search-image?query=professional%20author%20portrait%2C%20self-help%20writer%2C%20warm%20smile%2C%20modern%20headshot%2C%20business%20casual&width=40&height=40&seq=author-2&orientation=squarish'
    },
    {
      id: 3,
      name: 'Matt Haig',
      email: 'matt@example.com',
      booksCount: 5,
      totalSales: 987,
      revenue: 14805,
      joinDate: '2024-01-10',
      status: 'pending',
      bio: 'British author writing fiction and non-fiction',
      avatar: 'https://readdy.ai/api/search-image?query=professional%20author%20portrait%2C%20literary%20writer%2C%20creative%20expression%2C%20modern%20headshot%2C%20business%20casual&width=40&height=40&seq=author-3&orientation=squarish'
    }
  ];

  const handleAddNew = (type: string) => {
    setModalType(type);
    setShowAddModal(true);
  };

  const handleToggleFeature = (bookId: number) => {
    console.log(`Toggle feature for book ${bookId}`);
  };

  const handleStatusChange = (bookId: number, newStatus: string) => {
    console.log(`Change status for book ${bookId} to ${newStatus}`);
  };

  const renderBooks = () => (
    <div className="space-y-6">
      {/* Actions */}
      <div className="flex justify-between items-center">
        <div className="flex space-x-4">
          <button
            onClick={() => handleAddNew('book')}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer whitespace-nowrap"
          >
            <i className="ri-add-line mr-2"></i>
            Add New Book
          </button>
          <button className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer whitespace-nowrap">
            <i className="ri-upload-line mr-2"></i>
            Import Books
          </button>
          <button className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer whitespace-nowrap">
            <i className="ri-download-line mr-2"></i>
            Export Books
          </button>
        </div>
        <div className="flex items-center space-x-2">
          <select className="px-3 py-2 border border-gray-300 rounded-lg pr-8">
            <option>All Categories</option>
            {categories.map(cat => (
              <option key={cat.id} value={cat.name}>{cat.name}</option>
            ))}
          </select>
          <select className="px-3 py-2 border border-gray-300 rounded-lg pr-8">
            <option>All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
          </select>
        </div>
      </div>

      {/* Books Table */}
      <div className="bg-white rounded-lg shadow-md overflow-hidden">
        <table className="w-full">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inventory</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {books.map((book) => (
              <tr key={book.id} className="hover:bg-gray-50">
                <td className="px-6 py-4">
                  <div className="flex items-center">
                    <img src={book.cover} alt={book.title} className="w-12 h-18 object-cover object-top rounded" />
                    <div className="ml-4">
                      <div className="text-sm font-medium text-gray-900 flex items-center">
                        {book.title}
                        {book.featured && (
                          <span className="ml-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                            Featured
                          </span>
                        )}
                      </div>
                      <div className="text-sm text-gray-500">{book.author}</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 text-sm text-gray-900">{book.category}</td>
                <td className="px-6 py-4 text-sm text-gray-900">${book.price}</td>
                <td className="px-6 py-4">
                  <select
                    value={book.status}
                    onChange={(e) => handleStatusChange(book.id, e.target.value)}
                    className={`px-2 py-1 text-xs font-semibold rounded-full border-0 cursor-pointer ${
                      book.status === 'published'
                        ? 'bg-green-100 text-green-800'
                        : book.status === 'draft'
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-gray-100 text-gray-800'
                    }`}
                  >
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                  </select>
                </td>
                <td className="px-6 py-4 text-sm text-gray-900">{book.sales}</td>
                <td className="px-6 py-4 text-sm text-gray-900">
                  {book.rating > 0 ? (
                    <div className="flex items-center">
                      <span>{book.rating}</span>
                      <i className="ri-star-fill text-yellow-400 text-sm ml-1"></i>
                    </div>
                  ) : 'N/A'}
                </td>
                <td className="px-6 py-4 text-sm text-gray-900">
                  <span className={book.inventory < 50 ? 'text-red-600' : 'text-gray-900'}>
                    {book.inventory}
                  </span>
                </td>
                <td className="px-6 py-4 text-sm">
                  <div className="flex space-x-2">
                    <button className="text-blue-600 hover:text-blue-800 cursor-pointer">
                      <i className="ri-edit-line"></i>
                    </button>
                    <button className="text-green-600 hover:text-green-800 cursor-pointer">
                      <i className="ri-eye-line"></i>
                    </button>
                    <button
                      onClick={() => handleToggleFeature(book.id)}
                      className="text-yellow-600 hover:text-yellow-800 cursor-pointer"
                    >
                      <i className={`ri-${book.featured ? 'star-fill' : 'star-line'}`}></i>
                    </button>
                    <button className="text-red-600 hover:text-red-800 cursor-pointer">
                      <i className="ri-delete-bin-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderCategories = () => (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <button
          onClick={() => handleAddNew('category')}
          className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer whitespace-nowrap"
        >
          <i className="ri-add-line mr-2"></i>
          Add New Category
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {categories.map((category) => (
          <div key={category.id} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-900">{category.name}</h3>
              <span className={`px-2 py-1 text-xs font-semibold rounded-full ${category.color}`}>
                {category.bookCount} books
              </span>
            </div>
            <p className="text-sm text-gray-600 mb-4">{category.description}</p>
            <div className="flex space-x-2">
              <button className="flex-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 cursor-pointer whitespace-nowrap">
                <i className="ri-edit-line mr-2"></i>
                Edit
              </button>
              <button className="px-3 py-2 text-sm text-red-600 hover:text-red-800 cursor-pointer">
                <i className="ri-delete-bin-line"></i>
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderAuthors = () => (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <button
          onClick={() => handleAddNew('author')}
          className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer whitespace-nowrap"
        >
          <i className="ri-user-add-line mr-2"></i>
          Invite New Author
        </button>
      </div>

      <div className="bg-white rounded-lg shadow-md overflow-hidden">
        <table className="w-full">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Books</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Sales</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Join Date</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {authors.map((author) => (
              <tr key={author.id} className="hover:bg-gray-50">
                <td className="px-6 py-4">
                  <div className="flex items-center">
                    <img
                      src={author.avatar}
                      alt={author.name}
                      className="w-10 h-10 rounded-full object-cover object-top"
                    />
                    <div className="ml-4">
                      <div className="text-sm font-medium text-gray-900">{author.name}</div>
                      <div className="text-sm text-gray-500">{author.email}</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 text-sm text-gray-900">{author.booksCount}</td>
                <td className="px-6 py-4 text-sm text-gray-900">{author.totalSales}</td>
                <td className="px-6 py-4 text-sm text-gray-900">${author.revenue}</td>
                <td className="px-6 py-4 text-sm text-gray-900">{author.joinDate}</td>
                <td className="px-6 py-4">
                  <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                    author.status === 'active'
                      ? 'bg-green-100 text-green-800'
                      : author.status === 'pending'
                        ? 'bg-yellow-100 text-yellow-800'
                        : 'bg-gray-100 text-gray-800'
                  }`}>
                    {author.status}
                  </span>
                </td>
                <td className="px-6 py-4 text-sm">
                  <div className="flex space-x-2">
                    <button className="text-blue-600 hover:text-blue-800 cursor-pointer">
                      <i className="ri-mail-line"></i>
                    </button>
                    <button className="text-green-600 hover:text-green-800 cursor-pointer">
                      <i className="ri-eye-line"></i>
                    </button>
                    <button className="text-yellow-600 hover:text-yellow-800 cursor-pointer">
                      <i className="ri-money-dollar-circle-line"></i>
                    </button>
                    <button className="text-red-600 hover:text-red-800 cursor-pointer">
                      <i className="ri-user-unfollow-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const sections = [
    { id: 'books', label: 'Books', icon: 'ri-book-line' },
    { id: 'categories', label: 'Categories', icon: 'ri-price-tag-3-line' },
    { id: 'authors', label: 'Authors', icon: 'ri-user-line' }
  ];

  return (
    <div className="space-y-6">
      {/* Section Navigation */}
      <div className="flex space-x-1 bg-gray-100 p-1 rounded-lg w-fit">
        {sections.map((section) => (
          <button
            key={section.id}
            onClick={() => setActiveSection(section.id)}
            className={`flex items-center space-x-2 px-4 py-2 rounded-md font-medium text-sm whitespace-nowrap cursor-pointer ${
              activeSection === section.id
                ? 'bg-white text-blue-600 shadow-sm'
                : 'text-gray-600 hover:text-gray-800'
            }`}
          >
            <i className={section.icon}></i>
            <span>{section.label}</span>
          </button>
        ))}
      </div>

      {/* Content */}
      {activeSection === 'books' && renderBooks()}
      {activeSection === 'categories' && renderCategories()}
      {activeSection === 'authors' && renderAuthors()}

      {/* Add Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] overflow-y-auto">
            <div className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">
                  Add New {modalType.charAt(0).toUpperCase() + modalType.slice(1)}
                </h2>
                <button
                  onClick={() => setShowAddModal(false)}
                  className="text-gray-400 hover:text-gray-600 cursor-pointer"
                >
                  <i className="ri-close-line text-xl"></i>
                </button>
              </div>

              <form className="space-y-6">
                {modalType === 'book' && (
                  <>
                    {/* Book Type Selection */}
                    <div className="bg-gray-50 p-4 rounded-lg">
                      <label className="block text-sm font-medium text-gray-700 mb-3">Book Type</label>
                      <div className="flex space-x-6">
                        <label className="flex items-center cursor-pointer">
                          <input
                            type="radio"
                            value="ebook"
                            checked={bookType === 'ebook'}
                            onChange={(e) => setBookType(e.target.value)}
                            className="mr-2"
                          />
                          <span className="font-medium">Digital Ebook</span>
                        </label>
                        <label className="flex items-center cursor-pointer">
                          <input
                            type="radio"
                            value="physical"
                            checked={bookType === 'physical'}
                            onChange={(e) => setBookType(e.target.value)}
                            className="mr-2"
                          />
                          <span className="font-medium">Physical Book</span>
                        </label>
                      </div>
                    </div>

                    {/* Basic Information */}
                    <div className="bg-white border border-gray-200 rounded-lg p-6">
                      <h3 className="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                          <input
                            type="text"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter book title"
                            required
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Author *</label>
                          <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                            <option>Select author</option>
                            {authors.map((author) => (
                              <option key={author.id} value={author.id}>
                                {author.name}
                              </option>
                            ))}
                          </select>
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                          <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                            <option>Select category</option>
                            {categories.map((category) => (
                              <option key={category.id} value={category.name}>
                                {category.name}
                              </option>
                            ))}
                          </select>
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                          <div className="relative">
                            <span className="absolute left-3 top-2 text-gray-500">$</span>
                            <input
                              type="number"
                              step="0.01"
                              className="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="0.00"
                            />
                          </div>
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                          <input
                            type="text"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="978-0-000-00000-0"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Publication Date</label>
                          <input
                            type="date"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Language</label>
                          <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                            <option>English</option>
                            <option>Spanish</option>
                            <option>French</option>
                            <option>German</option>
                            <option>Italian</option>
                            <option>Other</option>
                          </select>
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">Pages</label>
                          <input
                            type="number"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Number of pages"
                          />
                        </div>
                        {/* Publisher field - only show for physical books */}
                        {bookType === 'physical' && (
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Publisher *</label>
                            <input
                              type="text"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Publisher name"
                              required
                            />
                          </div>
                        )}
                        {/* Stock quantity - only show for physical books */}
                        {bookType === 'physical' && (
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Stock Quantity *</label>
                            <input
                              type="number"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Available units"
                              required
                            />
                          </div>
                        )}
                      </div>
                      <div className="mt-6">
                        <label className="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          rows={3}
                          placeholder="Enter book description"
                          maxLength={500}
                        />
                      </div>
                      <div className="mt-6">
                        <label className="block text-sm font-medium text-gray-700 mb-2">Cover Image *</label>
                        <input
                          type="file"
                          accept="image/*"
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          required
                        />
                        <p className="text-xs text-gray-500 mt-1">Supported: JPG, PNG, WebP (Max 5MB)</p>
                      </div>
                    </div>

                    {/* Type-specific Information */}
                    {bookType === 'ebook' && (
                      <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 className="text-lg font-semibold text-blue-900 mb-4">Digital Content Settings</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Ebook File *</label>
                            <input
                              type="file"
                              accept=".epub,.pdf,.mobi"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required
                            />
                            <p className="text-xs text-gray-500 mt-1">Supported: EPUB, PDF, MOBI (Max 50MB)</p>
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">File Size (MB)</label>
                            <input
                              type="number"
                              step="0.1"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Auto-calculated"
                              readOnly
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">DRM Protection</label>
                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                              <option value="none">No DRM Protection</option>
                              <option value="basic">Basic DRM</option>
                              <option value="advanced">Advanced DRM</option>
                            </select>
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Download Limit</label>
                            <input
                              type="number"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="0 for unlimited"
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Sample Preview</label>
                            <input
                              type="file"
                              accept=".epub,.pdf,.mobi"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <p className="text-xs text-gray-500 mt-1">Optional: First few chapters for preview</p>
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Encryption Level</label>
                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                              <option value="low">Low Security</option>
                              <option value="medium">Medium Security</option>
                              <option value="high">High Security</option>
                            </select>
                          </div>
                        </div>
                        <div className="mt-6">
                          <label className="block text-sm font-medium text-gray-700 mb-3">Compatible Devices</label>
                          <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                            {['Kindle', 'iPad', 'Android', 'Desktop'].map((device) => (
                              <label key={device} className="flex items-center cursor-pointer">
                                <input type="checkbox" className="mr-2" defaultChecked />
                                <span className="text-sm">{device}</span>
                              </label>
                            ))}
                          </div>
                        </div>
                      </div>
                    )}

                    {bookType === 'physical' && (
                      <div className="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 className="text-lg font-semibold text-green-900 mb-4">Physical Book Details</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Book Format *</label>
                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                              <option>Hardcover</option>
                              <option>Paperback</option>
                              <option>Mass Market Paperback</option>
                              <option>Spiral Bound</option>
                            </select>
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Weight (g)</label>
                            <input
                              type="number"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Weight in grams"
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Length (cm)</label>
                            <input
                              type="number"
                              step="0.1"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Length"
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Width (cm)</label>
                            <input
                              type="number"
                              step="0.1"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Width"
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                            <input
                              type="number"
                              step="0.1"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Height"
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Shipping Cost</label>
                            <div className="relative">
                              <span className="absolute left-3 top-2 text-gray-500">$</span>
                              <input
                                type="number"
                                step="0.01"
                                className="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00"
                              />
                            </div>
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Print Quality</label>
                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                              <option>Standard</option>
                              <option>Premium</option>
                              <option>Luxury</option>
                            </select>
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Restock Alert Level</label>
                            <input
                              type="number"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Alert when stock below"
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                            <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8">
                              <option>New</option>
                              <option>Like New</option>
                              <option>Good</option>
                              <option>Fair</option>
                            </select>
                          </div>
                        </div>
                        <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Storage Location</label>
                            <input
                              type="text"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Warehouse section/shelf"
                            />
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <input
                              type="text"
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Supplier name"
                            />
                          </div>
                        </div>
                      </div>
                    )}
                  </>
                )}

                {modalType === 'category' && (
                  <div className="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Category Information</h3>
                    <div className="space-y-4">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                        <input
                          type="text"
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Enter category name"
                          required
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          rows={3}
                          placeholder="Enter category description"
                        />
                      </div>
                    </div>
                  </div>
                )}

                {modalType === 'author' && (
                  <div className="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Author Information</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input
                          type="text"
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Enter author name"
                          required
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input
                          type="email"
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="author@example.com"
                          required
                        />
                      </div>
                    </div>
                    <div className="mt-6">
                      <label className="block text-sm font-medium text-gray-700 mb-2">Biography</label>
                      <textarea
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        rows={4}
                        placeholder="Enter author biography"
                      />
                    </div>
                  </div>
                )}

                {/* Action Buttons */}
                <div className="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                  <button
                    type="button"
                    onClick={() => setShowAddModal(false)}
                    className="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 cursor-pointer whitespace-nowrap"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer whitespace-nowrap"
                  >
                    Create {modalType.charAt(0).toUpperCase() + modalType.slice(1)}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
