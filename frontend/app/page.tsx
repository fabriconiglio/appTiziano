import HeroSlider from '@/components/home/HeroSlider'
import FeatureBar from '@/components/home/FeatureBar'
import FeaturedBrands from '@/components/home/FeaturedBrands'
import FeaturedProducts from '@/components/home/FeaturedProducts'
import BannerStrip from '@/components/home/BannerStrip'
import TestimonialsSection from '@/components/home/TestimonialsSection'

export const dynamic = 'force-dynamic'

export default function HomePage() {
  return (
    <>
      <HeroSlider />
      <FeatureBar />
      <FeaturedBrands />
      <FeaturedProducts />
      <BannerStrip />
      <TestimonialsSection />
    </>
  )
}
