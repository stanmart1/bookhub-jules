
'use client';

export default function WelcomeHeader() {
  const userStats = {
    booksRead: 47,
    currentlyReading: 3,
    totalHours: 245,
    streak: 12
  };

  return (
    <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold mb-2">Welcome back, Sarah!</h1>
          <p className="text-blue-100 mb-4">Ready to continue your reading journey?</p>
          
          <div className="flex items-center space-x-6">
            <div className="text-center">
              <div className="text-2xl font-bold">{userStats.booksRead}</div>
              <div className="text-sm text-blue-100">Books Read</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold">{userStats.currentlyReading}</div>
              <div className="text-sm text-blue-100">Currently Reading</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold">{userStats.totalHours}</div>
              <div className="text-sm text-blue-100">Hours Read</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold">{userStats.streak}</div>
              <div className="text-sm text-blue-100">Day Streak</div>
            </div>
          </div>
        </div>
        
        <div className="hidden md:flex items-center space-x-4">
          <img 
            src="https://readdy.ai/api/search-image?query=professional%20woman%20reading%20book%20in%20modern%20library%20setting%2C%20warm%20lighting%2C%20cozy%20atmosphere%2C%20detailed%20portrait%2C%20contemporary%20style%2C%20soft%20focus%20background&width=120&height=120&seq=welcome-1&orientation=squarish"
            alt="Reading"
            className="w-24 h-24 rounded-full object-cover object-top border-4 border-white/20"
          />
        </div>
      </div>
    </div>
  );
}
