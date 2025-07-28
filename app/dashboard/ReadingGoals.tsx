
'use client';

export default function ReadingGoals() {
  const goals = [
    {
      title: 'Annual Reading Goal',
      current: 47,
      target: 60,
      unit: 'books',
      color: 'bg-blue-500'
    },
    {
      title: 'Monthly Pages',
      current: 1250,
      target: 1500,
      unit: 'pages',
      color: 'bg-green-500'
    },
    {
      title: 'Reading Streak',
      current: 12,
      target: 30,
      unit: 'days',
      color: 'bg-purple-500'
    }
  ];

  const achievements = [
    {
      title: 'Speed Reader',
      description: 'Read 5 books in a month',
      earned: true,
      icon: 'ri-flashlight-line'
    },
    {
      title: 'Diverse Reader',
      description: 'Read 5 different genres',
      earned: true,
      icon: 'ri-book-line'
    },
    {
      title: 'Consistent Reader',
      description: 'Read for 30 days straight',
      earned: false,
      icon: 'ri-calendar-line'
    },
    {
      title: 'Social Reader',
      description: 'Write 20 reviews',
      earned: false,
      icon: 'ri-chat-1-line'
    }
  ];

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-4">Reading Goals</h2>
      
      {/* Goals Progress */}
      <div className="space-y-4 mb-6">
        {goals.map((goal, index) => (
          <div key={index}>
            <div className="flex justify-between items-center mb-1">
              <h3 className="text-sm font-medium text-gray-700">{goal.title}</h3>
              <span className="text-sm text-gray-500">
                {goal.current}/{goal.target} {goal.unit}
              </span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div 
                className={`${goal.color} h-2 rounded-full transition-all duration-300`}
                style={{ width: `${Math.min((goal.current / goal.target) * 100, 100)}%` }}
              ></div>
            </div>
          </div>
        ))}
      </div>

      {/* Achievements */}
      <div>
        <h3 className="text-sm font-medium text-gray-700 mb-3">Achievements</h3>
        <div className="grid grid-cols-2 gap-2">
          {achievements.map((achievement, index) => (
            <div 
              key={index}
              className={`p-3 rounded-lg border-2 ${
                achievement.earned 
                  ? 'border-yellow-300 bg-yellow-50' 
                  : 'border-gray-200 bg-gray-50'
              }`}
            >
              <div className="flex items-center space-x-2 mb-1">
                <div className={`w-6 h-6 flex items-center justify-center ${
                  achievement.earned ? 'text-yellow-600' : 'text-gray-400'
                }`}>
                  <i className={`${achievement.icon} text-sm`}></i>
                </div>
                <h4 className="text-xs font-medium text-gray-900">{achievement.title}</h4>
              </div>
              <p className="text-xs text-gray-600">{achievement.description}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
