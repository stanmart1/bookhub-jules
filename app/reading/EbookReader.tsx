
'use client';

import { useState, useEffect } from 'react';

export default function EbookReader({ book, onClose }) {
  const [currentPage, setCurrentPage] = useState(book.currentPage || 1);
  const [fontSize, setFontSize] = useState(16);
  const [theme, setTheme] = useState('light');
  const [isSettingsOpen, setIsSettingsOpen] = useState(false);
  const [isTableOfContentsOpen, setIsTableOfContentsOpen] = useState(false);
  const [bookmarks, setBookmarks] = useState([]);
  const [notes, setNotes] = useState([]);
  const [isBookmarked, setIsBookmarked] = useState(false);

  // Mock content for demonstration
  const generatePageContent = (pageNum) => {
    const sampleTexts = [
      'In the realm of personal finance, few topics generate as much confusion and debate as the psychology of money. This book explores the strange ways people think about money and teaches you how to make better sense of one of life\'s most important topics.',
      'Money is everywhere, it affects all of us, and confuses most of us. Everyone thinks about it a little differently. It offers lessons on how to better understand one of life\'s most important topics.',
      'The premise of this book is that doing well with money has a little to do with how smart you are and a lot to do with how you behave. And behavior is hard to teach, even to really smart people.',
      'A genius who loses control of their emotions can be a financial disaster. The opposite is also true. Ordinary folks with no financial education can be wealthy if they have a handful of behavioral skills that have nothing to do with formal measures of intelligence.',
      'We all make crazy financial decisions. Making money and keeping money are two different skills. Getting money requires taking risks, being optimistic, and putting yourself out there. But keeping money requires the opposite: frugality and paranoia.',
      'Financial success is not a hard science. It\'s a soft skill, where how you behave is more important than what you know. This book is about that soft skill.',
      'The world is full of obvious things which nobody by any chance ever observes. But in the world of finance, the obvious is often exactly what we need to understand.',
      'Your personal experiences with money make up maybe 0.00000001% of what\'s happened in the world, but maybe 80% of how you think the world works.',
      'Every financial decision a person makes is justified by information they knew at the time. This is true even when the decision, in hindsight, was obviously wrong.',
      'The line between "inspiringly bold" and "foolishly reckless" can be a millimeter thick and only visible with hindsight. Risk and luck are doppelgängers.'
    ];

    return sampleTexts[pageNum % sampleTexts.length] || sampleTexts[0];
  };

  const nextPage = () => {
    if (currentPage < book.totalPages) {
      setCurrentPage(currentPage + 1);
    }
  };

  const previousPage = () => {
    if (currentPage > 1) {
      setCurrentPage(currentPage - 1);
    }
  };

  const toggleBookmark = () => {
    if (isBookmarked) {
      setBookmarks(bookmarks.filter(b => b.page !== currentPage));
    } else {
      setBookmarks([...bookmarks, { page: currentPage, title: `Page ${currentPage}` }]);
    }
    setIsBookmarked(!isBookmarked);
  };

  const goToPage = (pageNum) => {
    setCurrentPage(pageNum);
    setIsTableOfContentsOpen(false);
  };

  const progress = (currentPage / book.totalPages) * 100;

  const themeClasses = {
    light: 'bg-white text-gray-900',
    dark: 'bg-gray-900 text-gray-100',
    sepia: 'bg-yellow-50 text-gray-800'
  };

  const panelClasses = {
    light: 'bg-white text-gray-900 border-gray-200',
    dark: 'bg-gray-800 text-gray-100 border-gray-700',
    sepia: 'bg-yellow-50 text-gray-800 border-yellow-200'
  };

  const hoverClasses = {
    light: 'hover:bg-gray-100',
    dark: 'hover:bg-gray-700',
    sepia: 'hover:bg-yellow-100'
  };

  const buttonClasses = {
    light: 'hover:bg-gray-100 text-gray-700',
    dark: 'hover:bg-gray-700 text-gray-300',
    sepia: 'hover:bg-yellow-100 text-gray-700'
  };

  useEffect(() => {
    const handleKeyPress = (e) => {
      if (e.key === 'ArrowRight') nextPage();
      if (e.key === 'ArrowLeft') previousPage();
      if (e.key === 'Escape') onClose();
    };

    window.addEventListener('keydown', handleKeyPress);
    return () => window.removeEventListener('keydown', handleKeyPress);
  }, [currentPage]);

  return (
    <div className={`fixed inset-0 z-50 ${themeClasses[theme]} transition-colors duration-300`}>
      {/* Header */}
      <div className={`flex items-center justify-between p-4 border-b ${theme === 'light' ? 'border-gray-200' : theme === 'dark' ? 'border-gray-700' : 'border-yellow-200'}`}>
        <div className="flex items-center space-x-4">
          <button
            onClick={onClose}
            className={`p-2 rounded-lg transition-colors cursor-pointer ${buttonClasses[theme]}`}
          >
            <i className="ri-close-line text-xl"></i>
          </button>
          <div>
            <h1 className="font-semibold text-lg">{book.title}</h1>
            <p className={`text-sm ${theme === 'light' ? 'text-gray-600' : theme === 'dark' ? 'text-gray-400' : 'text-gray-600'}`}>{book.author}</p>
          </div>
        </div>

        <div className="flex items-center space-x-2">
          {/* Table of Contents */}
          <button
            onClick={() => setIsTableOfContentsOpen(!isTableOfContentsOpen)}
            className={`p-2 rounded-lg transition-colors cursor-pointer ${buttonClasses[theme]}`}
          >
            <i className="ri-list-unordered text-xl"></i>
          </button>

          {/* Bookmark */}
          <button
            onClick={toggleBookmark}
            className={`p-2 rounded-lg transition-colors cursor-pointer ${buttonClasses[theme]} ${isBookmarked ? 'text-blue-600' : ''}`}
          >
            <i className={`ri-bookmark-${isBookmarked ? 'fill' : 'line'} text-xl`}></i>
          </button>

          {/* Settings */}
          <button
            onClick={() => setIsSettingsOpen(!isSettingsOpen)}
            className={`p-2 rounded-lg transition-colors cursor-pointer ${buttonClasses[theme]}`}
          >
            <i className="ri-settings-3-line text-xl"></i>
          </button>
        </div>
      </div>

      {/* Settings Panel */}
      {isSettingsOpen && (
        <div className={`absolute top-16 right-4 rounded-lg shadow-lg p-4 w-80 z-10 border ${panelClasses[theme]}`}>
          <h3 className="font-semibold mb-4">Reading Settings</h3>

          {/* Font Size */}
          <div className="mb-4">
            <label className="block text-sm font-medium mb-2">Font Size</label>
            <div className="flex items-center space-x-2">
              <button
                onClick={() => setFontSize(Math.max(12, fontSize - 2))}
                className={`p-1 rounded cursor-pointer ${hoverClasses[theme]}`}
              >
                <i className="ri-subtract-line"></i>
              </button>
              <span className="text-sm">{fontSize}px</span>
              <button
                onClick={() => setFontSize(Math.min(24, fontSize + 2))}
                className={`p-1 rounded cursor-pointer ${hoverClasses[theme]}`}
              >
                <i className="ri-add-line"></i>
              </button>
            </div>
          </div>

          {/* Theme */}
          <div className="mb-4">
            <label className="block text-sm font-medium mb-2">Theme</label>
            <div className="flex space-x-2">
              {[
                { key: 'light', label: 'Light', icon: 'ri-sun-line' },
                { key: 'dark', label: 'Dark', icon: 'ri-moon-line' },
                { key: 'sepia', label: 'Sepia', icon: 'ri-contrast-line' }
              ].map((themeOption) => (
                <button
                  key={themeOption.key}
                  onClick={() => setTheme(themeOption.key)}
                  className={`flex-1 p-2 rounded text-sm cursor-pointer transition-colors ${
                    theme === themeOption.key
                      ? 'bg-blue-600 text-white'
                      : theme === 'light'
                        ? 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                        : theme === 'dark'
                        ? 'bg-gray-700 hover:bg-gray-600 text-gray-300'
                        : 'bg-yellow-100 hover:bg-yellow-200 text-gray-700'
                  }`}
                >
                  <i className={`${themeOption.icon} mr-1`}></i>
                  {themeOption.label}
                </button>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Table of Contents */}
      {isTableOfContentsOpen && (
        <div className={`absolute top-16 left-4 rounded-lg shadow-lg p-4 w-80 max-h-96 overflow-y-auto z-10 border ${panelClasses[theme]}`}>
          <h3 className="font-semibold mb-4">Table of Contents</h3>
          <div className="space-y-2">
            {book.content.chapters.map((chapter, index) => (
              <button
                key={index}
                onClick={() => goToPage(index * 50 + 1)}
                className={`w-full text-left p-2 rounded transition-colors cursor-pointer ${hoverClasses[theme]}`}
              >
                <div className="font-medium text-sm">{chapter.title}</div>
                <div className={`text-xs ${theme === 'light' ? 'text-gray-500' : theme === 'dark' ? 'text-gray-400' : 'text-gray-600'}`}>
                  {chapter.pages} pages
                </div>
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Main Content */}
      <div className="flex-1 flex">
        {/* Navigation Button - Left */}
        <button
          onClick={previousPage}
          disabled={currentPage === 1}
          className={`w-12 flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${hoverClasses[theme]}`}
        >
          <i className="ri-arrow-left-line text-xl"></i>
        </button>

        {/* Reading Area */}
        <div className="flex-1 max-w-4xl mx-auto p-8">
          <div
            className="prose prose-lg max-w-none leading-relaxed"
            style={{ fontSize: `${fontSize}px` }}
          >
            <div className="mb-8">
              <h2 className="text-2xl font-bold mb-4">
                Chapter {Math.floor(currentPage / 50) + 1}
              </h2>
              <div className="text-justify">
                {generatePageContent(currentPage)}
              </div>
            </div>

            {/* Mock additional content */}
            <div className="space-y-4">
              <p>
                This represents the content of page {currentPage}. In a real implementation,
                this would be the actual text content of the book, properly formatted and
                paginated.
              </p>
              <p>
                The reader includes features like bookmarking, note-taking, theme switching,
                and table of contents navigation to enhance the reading experience.
              </p>
            </div>
          </div>
        </div>

        {/* Navigation Button - Right */}
        <button
          onClick={nextPage}
          disabled={currentPage === book.totalPages}
          className={`w-12 flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${hoverClasses[theme]}`}
        >
          <i className="ri-arrow-right-line text-xl"></i>
        </button>
      </div>

      {/* Footer */}
      <div className={`p-4 border-t ${theme === 'light' ? 'border-gray-200' : theme === 'dark' ? 'border-gray-700' : 'border-yellow-200'}`}>
        <div className="flex items-center justify-between">
          <div className={`text-sm ${theme === 'light' ? 'text-gray-600' : theme === 'dark' ? 'text-gray-400' : 'text-gray-600'}`}>
            Page {currentPage} of {book.totalPages}
          </div>

          {/* Progress Bar */}
          <div className="flex-1 mx-8">
            <div className={`w-full rounded-full h-2 ${theme === 'light' ? 'bg-gray-200' : theme === 'dark' ? 'bg-gray-700' : 'bg-yellow-200'}`}>
              <div
                className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                style={{ width: `${progress}%` }}
              ></div>
            </div>
          </div>

          <div className={`text-sm ${theme === 'light' ? 'text-gray-600' : theme === 'dark' ? 'text-gray-400' : 'text-gray-600'}`}>
            {Math.round(progress)}% complete
          </div>
        </div>
      </div>
    </div>
  );
}
