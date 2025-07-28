
'use client';

import Header from '@/components/Header';
import AdminHeader from './AdminHeader';
import OverviewStats from './OverviewStats';
import UserManagement from './UserManagement';
import ContentManagement from './ContentManagement';
import SalesAnalytics from './SalesAnalytics';
import ReportsSection from './ReportsSection';
import SystemSettings from './SystemSettings';
import { useState } from 'react';

export default function AdminDashboard() {
  const [activeTab, setActiveTab] = useState('overview');

  const tabs = [
    { id: 'overview', label: 'Overview', icon: 'ri-dashboard-line' },
    { id: 'users', label: 'Users', icon: 'ri-user-line' },
    { id: 'content', label: 'Content', icon: 'ri-book-line' },
    { id: 'sales', label: 'Sales', icon: 'ri-bar-chart-line' },
    { id: 'reports', label: 'Reports', icon: 'ri-file-text-line' },
    { id: 'settings', label: 'Settings', icon: 'ri-settings-line' }
  ];

  const renderContent = () => {
    switch(activeTab) {
      case 'overview':
        return <OverviewStats />;
      case 'users':
        return <UserManagement />;
      case 'content':
        return <ContentManagement />;
      case 'sales':
        return <SalesAnalytics />;
      case 'reports':
        return <ReportsSection />;
      case 'settings':
        return <SystemSettings />;
      default:
        return <OverviewStats />;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <AdminHeader />
        
        {/* Navigation Tabs */}
        <div className="mt-8 border-b border-gray-200">
          <nav className="flex space-x-8">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`flex items-center space-x-2 py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap cursor-pointer ${
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                <i className={`${tab.icon} text-lg`}></i>
                <span>{tab.label}</span>
              </button>
            ))}
          </nav>
        </div>
        
        {/* Content */}
        <div className="mt-8">
          {renderContent()}
        </div>
      </div>
    </div>
  );
}
