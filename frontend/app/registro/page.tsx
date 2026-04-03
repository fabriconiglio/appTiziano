'use client'

import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { useState } from 'react'
import { ChevronRight } from 'lucide-react'
import { useAuth } from '@/lib/AuthContext'
import GoogleLoginButton from '@/components/GoogleLoginButton'

export default function RegistroPage() {
  const { register, isAuthenticated } = useAuth()
  const router = useRouter()
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [errors, setErrors] = useState<Record<string, string[]>>({})
  const [generalError, setGeneralError] = useState('')
  const [submitting, setSubmitting] = useState(false)

  if (isAuthenticated) {
    router.replace('/mi-cuenta')
    return null
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    setGeneralError('')
    setSubmitting(true)
    try {
      await register(name, email, password, passwordConfirmation)
      router.push('/mi-cuenta')
    } catch (err: any) {
      if (err?.errors) {
        setErrors(err.errors)
      } else {
        setGeneralError(err?.message || 'Ocurrió un error al crear la cuenta.')
      }
    } finally {
      setSubmitting(false)
    }
  }

  const fieldError = (field: string) =>
    errors[field]?.[0] ? (
      <p className="mt-1 text-xs" style={{ color: '#b91c1c' }}>{errors[field][0]}</p>
    ) : null

  return (
    <>
      <div
        style={{
          background: 'var(--color-dark)',
          padding: '56px 0 48px',
          borderBottom: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6">
          <nav className="mb-4 flex items-center gap-2 text-xs" style={{ color: '#888' }}>
            <Link href="/" style={{ color: '#888' }}>Inicio</Link>
            <ChevronRight size={12} />
            <span style={{ color: 'var(--color-primary)' }}>Crear cuenta</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Crear cuenta
          </h1>
        </div>
      </div>

      <section style={{ background: 'var(--color-bg)', padding: '64px 0' }}>
        <div className="max-w-md mx-auto px-6">
          <div className="p-8" style={{ background: 'var(--color-white)', border: '1px solid var(--color-border)' }}>
            <h2
              className="text-sm font-bold uppercase tracking-wide mb-6 text-center"
              style={{ color: 'var(--color-dark)' }}
            >
              Registrate para comprar
            </h2>

            {generalError && (
              <div className="mb-4 p-3 text-sm" style={{ background: '#fef2f2', color: '#b91c1c', border: '1px solid #fecaca' }}>
                {generalError}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                  Nombre completo
                </label>
                <input
                  id="name"
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  className="w-full px-4 py-2.5 text-sm outline-none"
                  style={{ border: '1px solid var(--color-border)', background: 'var(--color-bg)', color: 'var(--color-dark)' }}
                />
                {fieldError('name')}
              </div>
              <div>
                <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                  Email
                </label>
                <input
                  id="email"
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full px-4 py-2.5 text-sm outline-none"
                  style={{ border: '1px solid var(--color-border)', background: 'var(--color-bg)', color: 'var(--color-dark)' }}
                />
                {fieldError('email')}
              </div>
              <div>
                <label htmlFor="password" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                  Contraseña
                </label>
                <input
                  id="password"
                  type="password"
                  required
                  minLength={8}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full px-4 py-2.5 text-sm outline-none"
                  style={{ border: '1px solid var(--color-border)', background: 'var(--color-bg)', color: 'var(--color-dark)' }}
                />
                {fieldError('password')}
              </div>
              <div>
                <label htmlFor="password_confirmation" className="block text-xs font-semibold uppercase tracking-wider mb-1.5" style={{ color: 'var(--color-dark-soft)' }}>
                  Confirmar contraseña
                </label>
                <input
                  id="password_confirmation"
                  type="password"
                  required
                  minLength={8}
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  className="w-full px-4 py-2.5 text-sm outline-none"
                  style={{ border: '1px solid var(--color-border)', background: 'var(--color-bg)', color: 'var(--color-dark)' }}
                />
              </div>
              <button
                type="submit"
                disabled={submitting}
                className="w-full px-6 py-3 text-sm font-semibold uppercase tracking-wider transition-opacity hover:opacity-90 disabled:opacity-50"
                style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
              >
                {submitting ? 'Creando cuenta...' : 'Crear cuenta'}
              </button>
            </form>

            <div className="flex items-center gap-4 my-6">
              <div className="flex-1 h-px" style={{ background: 'var(--color-border)' }} />
              <span className="text-xs uppercase tracking-wider font-semibold" style={{ color: 'var(--color-dark-soft)' }}>o</span>
              <div className="flex-1 h-px" style={{ background: 'var(--color-border)' }} />
            </div>

            <GoogleLoginButton />

            <p className="mt-6 text-center text-sm" style={{ color: 'var(--color-dark-soft)' }}>
              ¿Ya tenés cuenta?{' '}
              <Link href="/ingresar" className="font-semibold underline" style={{ color: 'var(--color-primary)' }}>
                Iniciar sesión
              </Link>
            </p>
          </div>
        </div>
      </section>
    </>
  )
}
