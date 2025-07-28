
'use client';

import { useState } from 'react';

export default function UserManagement() {
  const [searchTerm, setSearchTerm] = useState('');
  const [filterRole, setFilterRole] = useState('all');
  const [filterStatus, setFilterStatus] = useState('all');
  const [selectedUsers, setSelectedUsers] = useState<number[]>([]);
  const [showUserModal, setShowUserModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [selectedUser, setSelectedUser] = useState<any>(null);
  const [editingUser, setEditingUser] = useState<any>(null);

  const users = [
    {
      id: 1,
      name: 'Sarah Johnson',
      email: 'sarah.johnson@email.com',
      role: 'reader',
      status: 'active',
      joinDate: '2024-01-15',
      booksRead: 23,
      lastActive: '2 hours ago',
      totalSpent: 456.78,
      reviews: 12,
      avatar: 'https://readdy.ai/api/search-image?query=professional%20woman%20portrait%2C%20friendly%20smile%2C%20modern%20headshot%2C%20business%20casual%2C%20clean%20background&width=40&height=40&seq=user-1&orientation=squarish'
    },
    {
      id: 2,
      name: 'Michael Chen',
      email: 'michael.chen@email.com',
      role: 'author',
      status: 'active',
      joinDate: '2024-02-03',
      booksRead: 8,
      lastActive: '1 day ago',
      totalSpent: 234.56,
      reviews: 5,
      avatar: 'https://readdy.ai/api/search-image?query=professional%20man%20portrait%2C%20confident%20expression%2C%20modern%20headshot%2C%20business%20casual%2C%20clean%20background&width=40&height=40&seq=user-2&orientation=squarish'
    },
    {
      id: 3,
      name: 'Emily Rodriguez',
      email: 'emily.rodriguez@email.com',
      role: 'reader',
      status: 'suspended',
      joinDate: '2024-01-28',
      booksRead: 15,
      lastActive: '5 days ago',
      totalSpent: 189.34,
      reviews: 8,
      avatar: 'https://readdy.ai/api/search-image?query=professional%20woman%20portrait%2C%20warm%20smile%2C%20modern%20headshot%2C%20business%20casual%2C%20clean%20background&width=40&height=40&seq=user-3&orientation=squarish'
    },
    {
      id: 4,
      name: 'David Williams',
      email: 'david.williams@email.com',
      role: 'moderator',
      status: 'active',
      joinDate: '2023-12-10',
      booksRead: 45,
      lastActive: '30 minutes ago',
      totalSpent: 678.90,
      reviews: 25,
      avatar: 'https://readdy.ai/api/search-image?query=professional%20man%20portrait%2C%20approachable%20expression%2C%20modern%20headshot%2C%20business%20casual%2C%20clean%20background&width=40&height=40&seq=user-4&orientation=squarish'
    },
    {
      id: 5,
      name: 'Lisa Thompson',
      email: 'lisa.thompson@email.com',
      role: 'reader',
      status: 'active',
      joinDate: '2024-03-12',
      booksRead: 12,
      lastActive: '3 hours ago',
      totalSpent: 298.45,
      reviews: 7,
      avatar: 'https://readdy.ai/api/search-image?query=professional%20woman%20portrait%2C%20genuine%20smile%2C%20modern%20headshot%2C%20business%20casual%2C%20clean%20background&width=40&height=40&seq=user-5&orientation=squarish'
    }
  ];

  const filteredUsers = users.filter(user => {
    const matchesSearch = user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         user.email.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesRole = filterRole === 'all' || user.role === filterRole;
    const matchesStatus = filterStatus === 'all' || user.status === filterStatus;
    return matchesSearch && matchesRole && matchesStatus;
  });

  const handleUserAction = (userId: number, action: string) => {
    const user = users.find(u => u.id === userId);
    if (action === 'view' && user) {
      setSelectedUser(user);
      setShowUserModal(true);
    } else if (action === 'edit' && user) {
      setEditingUser({...user});
      setShowEditModal(true);
    } else {
      console.log(`${action} user ${userId}`);
    }
  };

  const handleEditSave = () => {
    console.log('Saving user changes:', editingUser);
    // Here you would typically save to your backend
    setShowEditModal(false);
    setEditingUser(null);
  };

  const handleEditCancel = () => {
    setShowEditModal(false);
    setEditingUser(null);
  };

  const handleEditChange = (field: string, value: any) => {
    setEditingUser(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleSelectUser = (userId: number) => {
    setSelectedUsers(prev => 
      prev.includes(userId) 
        ? prev.filter(id => id !== userId)
        : [...prev, userId]
    );
  };

  const handleBulkAction = (action: string) => {
    console.log(`Bulk ${action} on users:`, selectedUsers);
    setSelectedUsers([]);
  };

  return (
    <div className="space-y-6">
      {/* Filters and Actions */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex flex-wrap items-center gap-4 mb-4">
          <div className="flex-1 min-w-64">
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i className="ri-search-line text-gray-400"></i>
              </div>
              <input
                type="text"
                placeholder="Search users..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          <select
            value={filterRole}
            onChange={(e) => setFilterRole(e.target.value)}
            className="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8"
          >
            <option value="all">All Roles</option>
            <option value="reader">Reader</option>
            <option value="author">Author</option>
            <option value="moderator">Moderator</option>
          </select>
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8"
          >
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="banned">Banned</option>
          </select>
          <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer whitespace-nowrap">
            <i className="ri-add-line mr-2"></i>
            Add User
          </button>
        </div>

        {/* Bulk Actions */}
        {selectedUsers.length > 0 && (
          <div className="flex items-center space-x-4 p-4 bg-blue-50 rounded-lg">
            <span className="text-sm text-blue-800">
              {selectedUsers.length} users selected
            </span>
            <div className="flex space-x-2">
              <button
                onClick={() => handleBulkAction('activate')}
                className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 cursor-pointer whitespace-nowrap"
              >
                Activate
              </button>
              <button
                onClick={() => handleBulkAction('suspend')}
                className="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 cursor-pointer whitespace-nowrap"
              >
                Suspend
              </button>
              <button
                onClick={() => handleBulkAction('delete')}
                className="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 cursor-pointer whitespace-nowrap"
              >
                Delete
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Users Table */}
      <div className="bg-white rounded-lg shadow-md overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left">
                  <input
                    type="checkbox"
                    checked={selectedUsers.length === filteredUsers.length}
                    onChange={() => {
                      if (selectedUsers.length === filteredUsers.length) {
                        setSelectedUsers([]);
                      } else {
                        setSelectedUsers(filteredUsers.map(u => u.id));
                      }
                    }}
                    className="cursor-pointer"
                  />
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  User
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Role
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Activity
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Spending
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredUsers.map((user) => (
                <tr key={user.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4">
                    <input
                      type="checkbox"
                      checked={selectedUsers.includes(user.id)}
                      onChange={() => handleSelectUser(user.id)}
                      className="cursor-pointer"
                    />
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <img
                        src={user.avatar}
                        alt={user.name}
                        className="w-10 h-10 rounded-full object-cover object-top"
                      />
                      <div className="ml-4">
                        <div className="text-sm font-medium text-gray-900">{user.name}</div>
                        <div className="text-sm text-gray-500">{user.email}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      user.role === 'author' ? 'bg-purple-100 text-purple-800' :
                      user.role === 'moderator' ? 'bg-blue-100 text-blue-800' :
                      'bg-gray-100 text-gray-800'
                    }`}>
                      {user.role}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      user.status === 'active' ? 'bg-green-100 text-green-800' :
                      user.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    }`}>
                      {user.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>{user.booksRead} books</div>
                    <div className="text-xs">{user.lastActive}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>${user.totalSpent}</div>
                    <div className="text-xs">{user.reviews} reviews</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div className="flex space-x-2">
                      <button
                        onClick={() => handleUserAction(user.id, 'view')}
                        className="text-blue-600 hover:text-blue-800 cursor-pointer"
                      >
                        <i className="ri-eye-line"></i>
                      </button>
                      <button
                        onClick={() => handleUserAction(user.id, 'edit')}
                        className="text-green-600 hover:text-green-800 cursor-pointer"
                      >
                        <i className="ri-edit-line"></i>
                      </button>
                      <button
                        onClick={() => handleUserAction(user.id, user.status === 'active' ? 'suspend' : 'activate')}
                        className="text-yellow-600 hover:text-yellow-800 cursor-pointer"
                      >
                        <i className={`ri-${user.status === 'active' ? 'pause' : 'play'}-circle-line`}></i>
                      </button>
                      <button
                        onClick={() => handleUserAction(user.id, 'delete')}
                        className="text-red-600 hover:text-red-800 cursor-pointer"
                      >
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

      {/* User Detail Modal */}
      {showUserModal && selectedUser && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-900">User Details</h2>
                <button
                  onClick={() => setShowUserModal(false)}
                  className="text-gray-400 hover:text-gray-600 cursor-pointer"
                >
                  <i className="ri-close-line text-xl"></i>
                </button>
              </div>

              <div className="space-y-6">
                <div className="flex items-center space-x-4">
                  <img
                    src={selectedUser.avatar}
                    alt={selectedUser.name}
                    className="w-16 h-16 rounded-full object-cover object-top"
                  />
                  <div>
                    <h3 className="text-lg font-medium text-gray-900">{selectedUser.name}</h3>
                    <p className="text-gray-600">{selectedUser.email}</p>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <span className="text-sm text-gray-900">{selectedUser.role}</span>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <span className="text-sm text-gray-900">{selectedUser.status}</span>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Join Date</label>
                    <span className="text-sm text-gray-900">{selectedUser.joinDate}</span>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Last Active</label>
                    <span className="text-sm text-gray-900">{selectedUser.lastActive}</span>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Books Read</label>
                    <span className="text-sm text-gray-900">{selectedUser.booksRead}</span>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Total Spent</label>
                    <span className="text-sm text-gray-900">${selectedUser.totalSpent}</span>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Reviews</label>
                    <span className="text-sm text-gray-900">{selectedUser.reviews}</span>
                  </div>
                </div>

                <div className="flex space-x-3">
                  <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer whitespace-nowrap">
                    Edit User
                  </button>
                  <button className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 cursor-pointer whitespace-nowrap">
                    Send Message
                  </button>
                  <button className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 cursor-pointer whitespace-nowrap">
                    Suspend User
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Edit User Modal */}
      {showEditModal && editingUser && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-900">Edit User</h2>
                <button
                  onClick={handleEditCancel}
                  className="text-gray-400 hover:text-gray-600 cursor-pointer"
                >
                  <i className="ri-close-line text-xl"></i>
                </button>
              </div>

              <form className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                  <input
                    type="text"
                    value={editingUser.name}
                    onChange={(e) => handleEditChange('name', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input
                    type="email"
                    value={editingUser.email}
                    onChange={(e) => handleEditChange('email', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
                  <select
                    value={editingUser.role}
                    onChange={(e) => handleEditChange('role', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8"
                  >
                    <option value="reader">Reader</option>
                    <option value="author">Author</option>
                    <option value="moderator">Moderator</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select
                    value={editingUser.status}
                    onChange={(e) => handleEditChange('status', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-8"
                  >
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="banned">Banned</option>
                  </select>
                </div>

                <div className="flex space-x-3 pt-4">
                  <button
                    type="button"
                    onClick={handleEditSave}
                    className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer whitespace-nowrap"
                  >
                    Save Changes
                  </button>
                  <button
                    type="button"
                    onClick={handleEditCancel}
                    className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer whitespace-nowrap"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Pagination */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex items-center justify-between">
          <div className="text-sm text-gray-700">
            Showing {filteredUsers.length} of {users.length} users
          </div>
          <div className="flex space-x-2">
            <button className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 cursor-pointer whitespace-nowrap">
              Previous
            </button>
            <button className="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer whitespace-nowrap">
              1
            </button>
            <button className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 cursor-pointer whitespace-nowrap">
              2
            </button>
            <button className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 cursor-pointer whitespace-nowrap">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
