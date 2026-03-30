'use client'

import { useState, useEffect, useCallback } from 'react'
import Link from 'next/link'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { Slider } from '@/lib/types'
import { getSliders } from '@/lib/api'

const fallbackSlides: Slider[] = [
  {
    id: 1,
    tag: 'Nueva Colección',
    title: 'Hidratación\nProfunda',
    subtitle: 'Tratamientos intensivos para cada tipo de cabello. Nutrición real desde la primera aplicación.',
    cta_text: 'Ver productos',
    cta_link: '/productos',
    image_url: null,
    image_mobile_url: null,
    bg_color: '#4a3020',
    order: 0,
  },
  {
    id: 2,
    tag: 'Línea Premium',
    title: 'Color que\nPerdura',
    subtitle: 'Coloración profesional de larga duración. Tonos vibrantes y protección total de la fibra capilar.',
    cta_text: 'Explorar línea',
    cta_link: '/productos',
    image_url: null,
    image_mobile_url: null,
    bg_color: '#16213e',
    order: 1,
  },
  {
    id: 3,
    tag: 'Uso Profesional',
    title: 'Control\nTotal del Frizz',
    subtitle: 'La fórmula infalible para un liso perfecto y una definición de rulos sin igual.',
    cta_text: 'Descubrir',
    cta_link: '/productos',
    image_url: null,
    image_mobile_url: null,
    bg_color: '#2d4a2d',
    order: 2,
  },
]

const accentColors = ['#c9bc9d', '#e0d8c8', '#fcf3e4']

export default function HeroSlider() {
  const [slides, setSlides] = useState<Slider[]>(fallbackSlides)
  const [current, setCurrent] = useState(0)
  const [animating, setAnimating] = useState(false)

  useEffect(() => {
    getSliders()
      .then((data) => {
        if (data.length > 0) setSlides(data)
      })
      .catch(() => {})
  }, [])

  const goTo = useCallback(
    (index: number) => {
      if (animating) return
      setAnimating(true)
      setCurrent(index)
      setTimeout(() => setAnimating(false), 600)
    },
    [animating]
  )

  const prev = () => goTo((current - 1 + slides.length) % slides.length)
  const next = useCallback(() => goTo((current + 1) % slides.length), [current, goTo, slides.length])

  useEffect(() => {
    if (slides.length <= 1) return
    const t = setInterval(next, 5500)
    return () => clearInterval(t)
  }, [next, slides.length])

  const slide = slides[current]
  const accent = accentColors[current % accentColors.length]

  const desktopImg = slide.image_url
  const mobileImg = slide.image_mobile_url || slide.image_url
  const hasAnyImage = desktopImg || mobileImg

  return (
    <section
      className="relative overflow-hidden"
      style={{
        height: 'clamp(480px, 75vh, 700px)',
        background: slide.bg_color,
        transition: 'background-color 0.8s ease',
      }}
    >
      {/* Desktop background image (hidden on mobile) */}
      {desktopImg && (
        <div
          className="absolute inset-0 hidden md:block"
          style={{
            backgroundImage: `url(${desktopImg})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            transition: 'opacity 0.8s ease',
          }}
        />
      )}
      {/* Mobile background image (hidden on desktop) */}
      {mobileImg && (
        <div
          className="absolute inset-0 block md:hidden"
          style={{
            backgroundImage: `url(${mobileImg})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            transition: 'opacity 0.8s ease',
          }}
        />
      )}
      {/* Dark overlay when image is present */}
      {hasAnyImage && (
        <div className="absolute inset-0" style={{ background: 'rgba(0,0,0,0.45)' }} />
      )}

      {/* Pattern overlay */}
      <div
        className="absolute inset-0 opacity-5"
        style={{
          backgroundImage:
            "url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")",
        }}
      />

      {/* Vertical accent line */}
      <div
        className="absolute left-0 top-0 bottom-0 w-1 opacity-60"
        style={{ background: `linear-gradient(to bottom, transparent, ${accent}, transparent)` }}
      />

      {/* Content */}
      <div className="relative z-10 h-full max-w-7xl mx-auto px-8 lg:px-16 flex flex-col justify-center">
        <div
          key={current}
          style={{
            opacity: animating ? 0 : 1,
            transform: animating ? 'translateY(20px)' : 'translateY(0)',
            transition: 'opacity 0.5s ease, transform 0.5s ease',
          }}
        >
          {/* Tag */}
          {slide.tag && (
            <div
              className="inline-flex items-center gap-2 mb-5 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest"
              style={{ border: `1px solid ${accent}`, color: accent }}
            >
              <span className="w-1.5 h-1.5 rounded-full" style={{ background: accent }} />
              {slide.tag}
            </div>
          )}

          {/* Title */}
          <h1
            className="font-bold mb-5 whitespace-pre-line"
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2.5rem, 6vw, 4.5rem)',
              color: '#ffffff',
              lineHeight: 1.05,
              textShadow: '0 2px 20px rgba(0,0,0,0.3)',
            }}
          >
            {slide.title}
          </h1>

          {/* Decorative line */}
          <div className="mb-6" style={{ width: '80px', height: '2px', background: accent }} />

          {/* Subtitle */}
          {slide.subtitle && (
            <p
              className="mb-8 max-w-lg text-base leading-relaxed"
              style={{ color: 'rgba(255,255,255,0.8)' }}
            >
              {slide.subtitle}
            </p>
          )}

          {/* CTA */}
          <Link
            href={slide.cta_link}
            className="inline-flex items-center gap-3 px-8 py-3.5 text-sm font-bold uppercase tracking-widest transition-all"
            style={{ background: accent, color: '#1a1a1a' }}
            onMouseEnter={(e) => {
              e.currentTarget.style.background = '#ffffff'
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.background = accent
            }}
          >
            {slide.cta_text}
            <ChevronRight size={16} />
          </Link>
        </div>
      </div>

      {/* Navigation arrows */}
      {slides.length > 1 && (
        <>
          <button
            onClick={prev}
            className="absolute left-4 lg:left-8 top-1/2 -translate-y-1/2 w-11 h-11 flex items-center justify-center transition-all z-20"
            style={{ border: '1px solid rgba(255,255,255,0.3)', color: '#fff' }}
            onMouseEnter={(e) => { e.currentTarget.style.background = 'rgba(255,255,255,0.15)' }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent' }}
          >
            <ChevronLeft size={20} />
          </button>
          <button
            onClick={next}
            className="absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 w-11 h-11 flex items-center justify-center transition-all z-20"
            style={{ border: '1px solid rgba(255,255,255,0.3)', color: '#fff' }}
            onMouseEnter={(e) => { e.currentTarget.style.background = 'rgba(255,255,255,0.15)' }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent' }}
          >
            <ChevronRight size={20} />
          </button>

          {/* Dots */}
          <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2.5 z-20">
            {slides.map((_, i) => (
              <button
                key={i}
                onClick={() => goTo(i)}
                className="transition-all"
                style={{
                  width: i === current ? '28px' : '8px',
                  height: '8px',
                  borderRadius: '4px',
                  background: i === current ? accent : 'rgba(255,255,255,0.4)',
                }}
              />
            ))}
          </div>
        </>
      )}
    </section>
  )
}
