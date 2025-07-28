
'use client';

export default function ReviewStats() {
  const stats = {
    totalReviews: 23,
    averageRating: 4.2,
    helpfulVotes: 156,
    followers: 42
  };

  const recentReviews = [
    {
      book: 'The Psychology of Money',
      rating: 5,
      date: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
      helpfulVotes: 12,
      excerpt: 'An excellent book that changed my perspective on money and investing...'
    },
    {
      book: 'Atomic Habits',
      rating: 4,
      date: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000),
      helpfulVotes: 8,
      excerpt: 'Practical advice on building good habits and breaking bad ones...'
    },
    {
      book: 'The Midnight Library',
      rating: 5,
      date: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000),
      helpfulVotes: 15,
      excerpt: 'A thought-provoking story about life choices and possibilities...'
    }
  ];

  const renderStars = (rating: number) => {
    const stars = [];
    for (let i = 1; i <= 5; i++) {
      stars.push(
        <i
          key={i}
          className={`ri-star-${i <= rating ? 'fill' : 'line'} text-yellow-400 text-sm`}
        ></i>
      );
    }
    return stars;
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-4">Review Impact</h2>
      
      {/* Stats Grid */}
      <div className="grid grid-cols-2 gap-4 mb-6">
        <div className="text-center p-3 bg-blue-50 rounded-lg">
          <div className="text-2xl font-bold text-blue-600">{stats.totalReviews}</div>
          <div className="text-sm text-gray-600">Reviews Written</div>
        </div>
        <div className="text-center p-3 bg-yellow-50 rounded-lg">
          <div className="text-2xl font-bold text-yellow-600">{stats.averageRating}</div>
          <div className="text-sm text-gray-600">Avg Rating</div>
        </div>
        <div className="text-center p-3 bg-green-50 rounded-lg">
          <div className="text-2xl font-bold text-green-600">{stats.helpfulVotes}</div>
          <div className="text-sm text-gray-600">Helpful Votes</div>
        </div>
        <div className="text-center p-3 bg-purple-50 rounded-lg">
          <div className="text-2xl font-bold text-purple-600">{stats.followers}</div>
          <div className="text-sm text-gray-600">Followers</div>
        </div>
      </div>

      {/* Recent Reviews */}
      <div>
        <h3 className="text-sm font-medium text-gray-700 mb-3">Recent Reviews</h3>
        <div className="space-y-3">
          {recentReviews.map((review, index) => (
            <div key={index} className="border-l-4 border-blue-500 pl-4 py-2">
              <div className="flex items-center justify-between mb-1">
                <h4 className="font-medium text-sm text-gray-900">{review.book}</h4>
                <div className="flex items-center space-x-1">
                  {renderStars(review.rating)}
                </div>
              </div>
              <p className="text-xs text-gray-600 mb-1">{review.excerpt}</p>
              <div className="flex items-center justify-between">
                <span className="text-xs text-gray-500">
                  {review.date.toLocaleDateString()}
                </span>
                <div className="flex items-center space-x-1">
                  <i className="ri-thumb-up-line text-xs text-gray-400"></i>
                  <span className="text-xs text-gray-500">{review.helpfulVotes}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
