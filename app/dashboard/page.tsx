
'use client';

import Header from '@/components/Header';
import WelcomeHeader from './WelcomeHeader';
import QuickActions from './QuickActions';
import ReadingProgress from './ReadingProgress';
import ActivityFeed from './ActivityFeed';
import ReadingGoals from './ReadingGoals';
import LibrarySection from './LibrarySection';
import PurchaseHistory from './PurchaseHistory';
import ReviewStats from './ReviewStats';
import NotificationCenter from './NotificationCenter';
import ReadingAnalytics from './ReadingAnalytics';

export default function Dashboard() {
  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <WelcomeHeader />
        
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
          {/* Left Column */}
          <div className="lg:col-span-2 space-y-8">
            <QuickActions />
            <ReadingProgress />
            <ActivityFeed />
            <ReadingAnalytics />
          </div>
          
          {/* Right Column */}
          <div className="space-y-8">
            <ReadingGoals />
            <NotificationCenter />
            <ReviewStats />
          </div>
        </div>
        
        {/* Full Width Sections */}
        <div className="mt-8 space-y-8">
          <LibrarySection />
          <PurchaseHistory />
        </div>
      </div>
    </div>
  );
}
