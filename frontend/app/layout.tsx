import type { Metadata } from 'next'
import './globals.css'
import Header from '@/components/layout/Header'
import Footer from '@/components/layout/Footer'
import { AuthProvider } from '@/lib/AuthContext'
import { CartProvider } from '@/lib/CartContext'

export const metadata: Metadata = {
  title: 'Tiziano Peluquería — Productos Profesionales',
  description: 'E-commerce de productos de peluquería profesional. Shampoos, acondicionadores, máscaras, coloración y más.',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="es" className="h-full antialiased">
      <body className="min-h-full flex flex-col">
        <AuthProvider>
          <CartProvider>
            <Header />
            <main className="flex-1">{children}</main>
            <Footer />
          </CartProvider>
        </AuthProvider>
      </body>
    </html>
  )
}
