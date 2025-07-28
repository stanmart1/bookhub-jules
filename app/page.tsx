
'use client';

import Header from '@/components/Header';
import HeroSection from '@/components/HeroSection';
import AboutSection from '@/components/AboutSection';
import FeaturedBooks from '@/components/FeaturedBooks';
import EReaderShowcase from '@/components/EReaderShowcase';
import BlogSection from '@/components/BlogSection';
import Footer from '@/components/Footer';

export default function Home() {
  return (
    <div className="min-h-screen bg-white">
      <Header />
      <HeroSection />
      <AboutSection />
      <FeaturedBooks />
      <EReaderShowcase />
      <BlogSection />
      <Footer />
    </div>
  );
}
